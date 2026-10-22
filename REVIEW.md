# TeamPortal — code review

Reviewed: `team-portal/` (Angular 15 frontend + PHP/Slim 4 backend).

**Context used:** production runs PHP 8.5 behind Apache next to the WordPress site on *antagonist*;
development is Docker Compose. WordPress tables live in `$_ENV['WPDBNAME']`, TeamPortal tables in
`$_ENV['DBNAME']` (same MySQL server).

**How it was verified**

- Angular: `ng build --configuration production` passes before and after the changes (no TS errors).
  `ng test` cannot be run here — `src/karma.conf.js` pins `browsers: ['Chrome']` with
  `singleRun: false`, and no headless browser is available.
- PHP: **no PHP runtime is available in the review environment** (`php` and `composer` are not
  installed, Docker is not attached to this WSL distribution). All PHP findings below are from
  reading the code, not from executing it. The PHPUnit suite could therefore not be run — please
  run `composer install && vendor/bin/phpunit` before merging.
- Note: the editor auto-formats PHP on save, so touched PHP files are reflowed in the diff. The
  formatter was checked for string-interpolation hazards (single → double quotes) and is safe; the
  semantic changes are listed separately below.

---

## 1. Fixed in this change set

### 1.1 Endpoints that could not work at all

| File | Problem | Fix |
|---|---|---|
| `php/index.php:161`, `php/UseCases/ScheduledTasks/DailyTasks.php` | The route `/tasks/daily-tasks` pointed at `UseCases\DailyTasks`, which does not exist → 500. `deploy.sh:41` calls exactly that URL. | Added `DailyTasks` use case (runs `SynchronizeWedstrijden`). |
| `php/UseCases/WeekOverzicht/GetWeekOverzicht.php:30-31` | Type hints `GateWays\BarcieGateway` / `GateWays\ZaalwachtGateway` (capital **W**). Composer autoloads via **classmap only**, and a classmap lookup is case-sensitive → PHP-DI cannot resolve the constructor → `/week-overzicht` 500s. | `Gateways\…`. |
| `php/UseCases/WeekOverzicht/GetWeekOverzicht.php:109,132,144` | `for ($date = clone $startDate; $date <= $endDate; $date->modify('+1 day'))`. `DateTime::modify()` returns a **new** object; `$date` is never mutated → infinite loop for a future date, zero iterations (empty export) for a past date. | `$date = $date->modify('+1 day')`. |
| `php/UseCases/Calendar/GetCalendar.php:84` | `$telWedstrijd->team1 . ' ' . $telWedstrijd->team2` — `Team` has no `__toString()` → `Error` for every teller on the calendar. Line 95 already did it correctly. | `->team1->naam . ' ' . ->team2->naam`. |
| `php/Gateways/AanwezigheidGateway.php` | Every query still joined the **removed Joomla tables** `J3_users` / `J3_usergroups` / `J3_user_usergroup_map`. The whole "wedstrijd-overzicht" attendance feature was dead. | Rewritten against `wp_users` / `wp_usermeta` (`team`, `coach_van`) / `wp_posts` (`post_type = 'team'`), matching the mapping already used in `TelFluitGateway`. |
| `php/Gateways/TelFluitGateway.php` | `GetAllFluitEnTelbeurten()`, `GetFluitbeurten()`, `GetTelbeurten()` still queried `J3_users` → the match-sync task and `/calendar` were dead. `GetFluitEnTelbeurtenForCalender()` used `DBNAME. teamportal_wedstrijden` (space + wrong case → table not found on a case-sensitive filesystem). | All four use WordPress; the three near-identical selects were collapsed into one private `BuildWedstrijdQuery()` helper. |
| `php/Gateways/SwapGateway.php:53-59,83-89` | `GetSwapsById()` and `RemoveSwaps()` used columns `scheduleid` / `userid`, which do not exist in `teamtaken_swaps` (`id, taskToSwapId, swapForTaskId, userWhoProposedId, otherUserId, date, shift`) → `/swaps/{id}` always SQL-errored. | Query the real columns; delete by `id`. |
| `php/Gateways/ZaalwachtGateway.php:119` | `GetZaalwachtIndeling()` joined `J3_usergroups` on a non-existent `Z.team_id` column, and returned an always-empty array (it filled `$rows` but returned `$response`). | Rewritten against `wp_posts`; now returns the indeling. |
| `php/UseCases/Barcie/AddBardag.php:24` | `CreateDateTime()` returns `null` on failure, never `false` → the guard never fired and `null` was passed to `GetYmdNotation(DateTime)` → `TypeError`. The message also interpolated `$data->data` instead of `$data->date`. | `if ($date === null)`, correct property. |
| `php/UseCases/MijnOverzicht/Models/ZaalwachtModel.php:13` | `$zaalwacht->team` does not exist (`Zaalwacht` has `eersteZaalwacht` / `tweedeZaalwacht`) → `Error: Call to a member function GetSkcNaam() on null`. | Use `eersteZaalwacht`, null-safe. |
| `php/Entities/Wedstrijddag.php:41` | `AddWedstrijd()` appended the match to `speeltijden[0]` instead of the slot it had just created → the second time slot of a day was empty and matches showed at the wrong time. | Append to the newly created slot. |
| `php/UseCases/Models/WedstrijdModel.php:53` | `$this->isTellers = $this->tellers === $user->team->GetShortNotation();` compares an **array** with a string → always `false`. | `in_array(...)`. |
| `php/UseCases/WedstrijdOverzicht/GetWedstrijdOverzicht.php:218` | The "onbekend" filter compared `Aanwezigheid::$id` (the **attendance row** id) with the person's id → teammates who *had* reported were never removed from the list. | Compare `$aanwezigheid->persoon->id`. |
| `php/Gateways/WordPressGateway.php` | `GetAllSpelers()` still queried `J3_users` / `J3_comprofiler`. | Rewritten against `wp_users`. |
| `php/UseCases/Barcie/GetBarcieBeschikbaarheden.php:53-55` | `usort($result->Onbekend, …)` sorted an always-empty array; the very next line overwrote `Onbekend` with the unsorted remainder. | Sort the source array, then assign. |

### 1.2 Data corruption (silent wrong values in the database / in the API)

| File | Problem | Fix |
|---|---|---|
| `php/Gateways/FluitBeschikbaarheidGateway.php:139-151` | `Update()` wrote a PHP **bool** into `is_beschikbaar ENUM('Ja','Nee','Onbekend')`, while `Insert()` wrote `"Ja"/"Nee"` and both readers test `=== "Ja"`. After any update the value was `1`/`0` → **a member's fluit/tel availability always read back as "not available"**. | Write `"Ja"/"Nee"/"Onbekend"`. |
| `php/Gateways/BarcieGateway.php:169-179` | `InsertBeschikbaarheid()` wrote a bool into `is_beschikbaar ENUM('Ja','Nee')` (only `Update()` used the strings). | Write `"Ja"/"Nee"`. |
| `php/Gateways/AanwezigheidGateway.php` | `Insert()`/`Update()` wrote a bool into `is_aanwezig ENUM('Ja','Nee')`. | Write `"Ja"/"Nee"`. |
| `php/Gateways/FluitBeschikbaarheidGateway.php:87-88` | `GetBeschikbaarheid()` used `$rows[0]->id` (the availability-row id) as the **person** id, and dropped the time from the returned date. | Use `$rows[0]->userId`, keep the time. |
| `php/Gateways/BarcieGateway.php:234-270` | `GetBardiensten()` selected neither `D.id` nor the e-mail address → undefined property + `null` day id on every bardienst. | Added `D.id` and `M.email` to the select. |
| `php/Entities/Team.php:14` | `public int $niveau;` was only assigned for SKC teams → for any opponent the Excel export hit `Error: Typed property … must not be accessed before initialization`. | `public ?int $niveau = null;` + `GetNiveau(null)` returns `""`. |
| `php/UseCases/Teamtakenco/GetZaalwachtTeams.php:38` | Set `$newTeam->aantal` while `TeamModel` declares `$aantalZaalwachten` → the declared field stayed uninitialised (absent from the JSON) and `aantal` was an undeclared dynamic property. | Both fields are set; `TeamModel` declares them. |
| `php/Gateways/WordPressGateway.php:320` | `MapToPersoon()` did `$result[] = $persoon;` on an undefined `$result` → warning on every call. | Removed. |
| `php/Gateways/WordPressGateway.php:130-136` | `GetUserByEmail()` did `get_user_by('email', …)->data->ID` → fatal on an unknown address (the weekly-mail sender addresses are hard-coded). | Explicit exception. |
| `php/Gateways/WordPressGateway.php:198,227` | `$userMeta['team'][0]` on a user without a team meta → undefined key; `GetTeamgenoten()` used an uninitialised `$teamObjects`. | Guards + initialisation. |

### 1.3 Angular bugs

| File | Problem | Fix |
|---|---|---|
| `src/app/teamtakenco/selecteer-scheidsrechter/selecteer-scheidsrechter.component.html:15` | `*ngFor="let type of scheidsrechters; index as i"` — the `index as` microsyntax was removed in Angular 5; `i` compiled to `undefined` → `getScheidsrechtersByKeuze(undefined, 'Ja')` → `TypeError`. The referee picker rendered **no options at all**. | `let i = index`. |
| `src/app/barcie/barcie-indeling/barcie-indeling.component.html:11` | `(ngModelChange)` on an input that uses `formControlName` (no `ngModel` directive) → the binding compiled to a DOM listener for an event that is never dispatched → **`onDateSelection()` never ran, so no bardag could ever be created**. | `(dateChange)` (ng-bootstrap emits an `NgbDate`, which is what `BarcieService.AddBarcieDag()` expects). |
| `src/app/mijn-overzicht/mijn-overzicht/mijn-overzicht.component.html:37,80,81,94` | `user.team.naam` / `user.naam` were dereferenced while `user` is still `undefined` (it is set by a separate `GetCurrentUser()` call) → template `TypeError`, the overview view failed to render. | `user && …` guards. |
| `src/app/mijn-overzicht/mijn-overzicht/mijn-overzicht.component.ts:94`, `src/app/mijn-overzicht/ruil-lijst/ruil-lijst.component.ts:81-82` | `obj.otherUserId === this.user.id` — PDO returns MySQL INT columns as **strings** (no `PDO::ATTR_EMULATE_PREPARES = false`), `user.id` is a JSON number → `"42" === 42` is false. The "Proposed to me" / "My proposals" lists were **always empty**, so swaps could never be accepted, rejected or deleted. | `Number(obj.otherUserId) === Number(this.user.id)`. |
| `src/app/app.component.ts:81-85`, `src/app/mijn-overzicht/mijn-overzicht/mijn-overzicht.component.ts:79-83` | `ngOnInit()` was called from inside its own subscription to a global `Subject` → every login added another subscription (never unsubscribed) and re-fired the same HTTP calls N times. | Extracted `loadMenuItems()` / `loadOverview()`; the subscription is created once. |
| `src/app/app.component.ts:67-73` | Every 401 emitted `setIsAuthenticated(false)` → a **new** login modal each time. An anonymous first visit fires 3 parallel requests → 3 stacked modals. | `loginModalOpen` guard. |
| `src/app/statistieken/statistieken.component.html:40` | `(sort)="onSort($event)"` — `StatistiekenComponent` has no `onSort` → `TypeError` when the footer cell is clicked. | Binding removed (`NgbSortable` sorts internally). |
| `src/app/barcie/barcie-indeling/barcie-indeling.component.ts:82` | `barciedag.shifts[shift - 1]` — positional lookup. `barcie_schedule_map` only contains shifts that still have staff, so shift numbers have gaps (1, 3, …) → the wrong shift was toggled or `TypeError`. `DeleteAanwezigheid()` (L106) already did it correctly. | Look the shift up by its number. |
| `src/app/mijn-overzicht/ruil-lijst/ruil-lijst.component.ts` | `proposedShifts: Set<string>` was filled with numbers (`value.id`) and DB strings (`swap.swapForTaskId`) → the "Proposed" badge never matched. | Normalised to `String`. |

### 1.4 PHP 8.x correctness (production runs 8.5)

| File | Problem | Fix |
|---|---|---|
| `php/Common/DateFunctions.php:19,26` | `strftime()` — deprecated since PHP 8.1 and **removed in PHP 9**; it also depends on the `nl_NL` locale being installed in the container. Used for every date in every e-mail and on the availability screens. | Explicit Dutch day/month tables; byte-identical output (`%e` padding reproduced). |
| `php/Entities/Enumerations/Niveau.php`, `php/RouteObjects/CrudRoute.php` | `case "eredivisie";` — the semicolon form of a `case` label is deprecated as of PHP 8.5 → an `E_DEPRECATED` per case label, on essentially every request (`Niveau::GetNiveauByString()` runs at class-load time). | `case "eredivisie":`. |
| `php/RouteObjects/CrudRoute.php:13-25` | `FillBody()` checked `if ($data === false)` instead of `if ($jsonData === false)`, so a `json_encode()` failure wrote an empty body with a 200. | Check `$jsonData`. |
| `php/RouteObjects/CrudRoute.php` | `FillBody(Response $response, …)` was type-hinted against `GuzzleHttp\Psr7\Response`, while the callers (`GetRoute`/`PostRoute`/`DeleteRoute`) pass a PSR-7 response from Slim. | `Psr\Http\Message\ResponseInterface` — the type the callers already declare. |
| `php/Entities/Team.php:20`, `php/Entities/Barcie/Bardienst.php`, `Barshift.php`, `Barciebeschikbaarheid.php`, `php/Entities/Zaalwacht/Zaalwacht.php:14`, `php/UseCases/Models/Beschikbaarheidssamenvatting.php:12`, `php/UseCases/Calendar/GetCalendar.php` | Implicit nullable parameters (`int $id = null`) — deprecated as of PHP 8.4. | `?int $id = null` etc. |
| `php/Entities/Personen/Persoon.php:23-24` | `$voornaam` / `$afkorting` were **dynamic properties** (deprecated in PHP 8.2) and are returned by nearly every endpoint. | Declared. |
| `php/Entities/Personen/Persoon.php:15` | `public string $spelertijd;` was never initialised → `Error` on read. | Default `''`. |
| `php/Entities/Personen/Barlid.php:8` | `public ?bool $isBhv;` was never initialised → the field silently disappeared from the JSON of `/barcie/beschikbaarheden`. | Default `null`. |
| `php/UseCases/Joomla/GetUsers.php:19` | `strlen(null)` — deprecation since PHP 8.1. | `(string)` cast. |

### 1.5 Robustness, security and hygiene

| File | Problem | Fix |
|---|---|---|
| `php/Gateways/EmailGateway.php:56,159` | `PrintEmail()` echoed the **complete** e-mail (sender, receiver, subject and body) into the HTTP response of `/tasks/queue-weekly-emails` and `/tasks/send-emails`. Those routes are `AuthorizationRole::UNREGISTERED` → **an anonymous visitor could read every member's name, e-mail address and weekly schedule**. | Output moved to `error_log` (the endpoints now return an empty body, which is what a cron needs). |
| `php/Gateways/EmailGateway.php:25` | `if (!$emails) { continue; }` inside the loop — always false; the intent was `if (!$email)`. | Fixed. |
| `php/Gateways/EmailGateway.php:97` | The dedupe query interpolated `$email->signature` into the SQL string. | Bound parameter. |
| `php/UseCases/WeekOverzicht/ExcelExport.php:359` | `error_log(print_r($this->Spreadsheet, true))` dumped the entire spreadsheet (all member names) into the PHP error log on every export. | Removed. |
| `php/UseCases/WeekOverzicht/ExcelExport.php:269-271` | `if (strstr(...) !== false) { return; }` — `return` aborted the whole day's schema, so **all remaining matches of that day were silently dropped** from the export. | `continue`. |
| `php/UseCases/WeekOverzicht/ExcelExport.php:231` | `$this->tweedeZaalwachtRow += $barlid->shift - 1;` used a loop variable after the loop → undefined variable when a shift had no barleden. | Removed (the row offset was never meaningful). |
| `php/UseCases/WeekOverzicht/ExcelExport.php:220` | `PaintCell('C' . $this->tweedeZaalwachtRow, …)` painted a different cell than the one that was written. | Same cell reference. |
| `php/Common/Curl.php` | Only `CURLOPT_CONNECTTIMEOUT` was set — a slow server could hang a request indefinitely; `curl_exec()` returning `false` was passed to `HttpResponse(string $package)` → `TypeError`; errors were `echo`ed into the response. | `CURLOPT_TIMEOUT`, explicit exception. |
| `php/Common/Models/HttpResponse.php:13,23` | `explode("\r\n\r\n", $package)` dropped everything after the first blank line of the body; a header line without `": "` → undefined offset. | Limit 2 + guard. |
| `php/Common/Database.php:24-40` | `$this->options` was built but never used (the options were passed inline instead). | Uses `$this->options`. |
| `php/UseCases/Barcie/AddBarcieAanwezigheid.php:41` | `InsertBardienst($bardienst, $bardag->id)` — the interface takes one argument; the second was silently discarded. | One argument. |
| `php/UseCases/Swap/Models/ShiftModel.php` | `$date` / `$shift` were typed and never initialised, but the client does not always post them. | Nullable with default. |
| `php/Entities/Enumerations/Niveau.php` / `php/Entities/Personen/Barlid.php` / `php/Entities/Wedstrijd.php` / `php/Entities/Wedstrijddag.php` | `usort` comparators returned a **bool** instead of an int → invalid comparator, ordering not guaranteed. | `int` + `<=>`. |
| `php/Entities/Team.php:128` | `file_get_contents("skc-teams.json")` — relative to the CWD, not the file. | `__DIR__`-relative. |
| `php/UseCases/WeekOverzicht/GetWeekOverzicht.php:5` | `require_once('ExcelExport.php')` — CWD-dependent, and a latent "Cannot declare class … already in use" when the composer classmap loads the same file first. | `__DIR__`-relative. |
| `php/UseCases/WeekOverzicht/GetWeekOverzicht.php:45` | `$data->datum` was read before the `$data == null` check. | Order fixed. |
| `php/Gateways/TelFluitGateway.php` (`GetTellers`) | `new Team($rows[0]->…)` on an empty result → fatal. | Guard. |
| `php/Gateways/TelFluitGateway.php` (`Insert`/`Update`) | `$wedstrijd->tellers[0]` / `[1]` without a count guard. | `?? null`. |

### 1.6 Tests

| File | Problem | Fix |
|---|---|---|
| `php/phpunit.xml` | No `<testsuite>` element → PHPUnit 10 had **nothing to run**. The schema pointed at PHPUnit 9.3 while `composer.json` requires `^10.3`, and `<coverage><include>` was renamed to `<source><include>` in PHPUnit 10. | Rewritten for PHPUnit 10 with a `Tests` suite and `bootstrap="vendor/autoload.php"`. |
| `php/Tests/BarcieTest.php` | The test asserted nothing (`$response = null; $this->assertNull($response);`) and never called `Execute()` — a false green. The real expectations were commented out. | Four real tests: three validation errors + "the dienst is stored". |
| `php/Tests/Mocks/Gateways/JoomlaGateway.php` | Dead leftover from the Joomla era: never referenced, does not implement `IWordPressGateway`, and the file name does not match the class name. | Deleted. |

---

## 2. Not fixed — needs a decision or more context

### 2.1 Security (high)

1. **`/tasks/*` and `/swaps/*` are `AuthorizationRole::UNREGISTERED`** (`php/index.php:147-162`).
   `/tasks/send-emails` triggers a real mail run, and `/swaps/swaptaak` (`AcceptSwaps`) rewrites
   `barcie_schedule_map.user_id` for arbitrary task ids — i.e. **an unauthenticated visitor can
   reassign or delete other people's bar duties and trigger the weekly mail run.**
   *Not changed* because the production cron calls these endpoints unauthenticated; adding a role
   would silently stop the weekly e-mails. **Recommended:** require a shared secret header (e.g.
   `X-Cron-Token`) on `/tasks/*` and `AuthorizationRole::USER` + an ownership check on `/swaps/*`,
   and configure the same token in the cron and in `deploy.sh`.

2. **`impersonationId`** (`php/Gateways/WordPressGateway.php:81-84`) is honoured for any
   WordPress administrator and is added by the Angular interceptor to *every* request
   (`src/app/core/interceptors/add-impersonation.interceptor.ts`). There is no audit trail of who
   impersonated whom. *Recommended:* log every impersonation and restrict the parameter to the
   handful of endpoints that need it.

3. **`src/app/core/interceptors/default-headers.interceptor.ts:17-23`** forces
   `Content-Type: application/json` onto GET requests → a CORS preflight on every GET in
   development. *Recommended:* only set it on requests with a body.

4. **`src/app/email-detail/email-detail.component.html:22`** renders `[innerHTML]="email.body"`.
   Angular's `DomSanitizer` strips scripts, so this is not an XSS, but it does silently rewrite
   legitimate mail formatting. *Recommended:* render the body as text, or sanitise it server-side.

### 2.2 Correctness that I could not safely change

5. **`php/UseCases/MijnOverzicht/GetMijnOverzicht.php:123-137`** — `new Wedstrijddag($wedstrijd->timestamp)`
   with a non-nullable `DateTime`, while `Wedstrijd::$timestamp` is `?DateTime`. A Nevobo match
   without a timestamp → `TypeError`. The same loop is guarded for the *speelwedstrijden* but not
   for the fluit/tel or coach loops. **Needs a product decision:** should matches without a
   timestamp be shown at all?

6. **`php/UseCases/MijnOverzicht/GetMijnOverzicht.php:78`** and
   **`php/UseCases/WeekOverzicht/GetWeekOverzicht.php`** — the `usort` comparators were fixed to
   return an int, but note that `Wedstrijd::Compare()` puts matches **without** a timestamp first.
   If a different order is intended, the comparator needs to change.

7. **`php/Entities/Email/Email.php:40-45`** — `Build()` unconditionally replaces the sender with
   "SKC Volleybal TeamTaken", even though `Scheidsrechtersmail` sets a Scheidsco sender. The
   `{{afzender}}` placeholder therefore shows a different name than the `From:` header.
   *Not changed* because it changes the outgoing mail.

8. **`php/Entities/Email/Samenvattingsmail.php:48`** — the title contains the current date, and
   `Email::CalculateSignature()` hashes sender+receiver+title+body. Running
   `queue-weekly-emails` on two different days therefore produces **two different signatures for
   identical content** → the summary mail is sent twice. *Recommended:* drop the date from the
   title (or from the signature).

9. **`php/Gateways/EmailGateway.php:80-92`** — `SendQueuedEmails()` has no `try/catch` although
   PHPMailer runs with exceptions enabled. An SMTP error aborts the loop **before**
   `MarkEmailAsSent()` → the mail is sent again on the next run. *Recommended:* mark the mail as
   sent in a `finally`, or wrap each mail in its own `try/catch`.

10. **`php/Common/Utilities.php:26`** — `if (strpos($template, $placeholder) == -1)` never fires
    (`strpos` returns `false`, and `false == -1` is `false`). The "placeholder not found" guard is
    dead. *Careful:* the four mail classes also pass `Placeholder::USER_ID` (`{{userId}}`), which
    exists in **no** template — so simply fixing the comparison to `=== false` would make those
    mails throw immediately. Both need to be fixed together.

11. **`php/UseCases/FluitBeschikbaarheid/UpdateFluitBeschikbaarheid.php:30`** —
    `in_array($isBeschikbaar, [true, false, null])` is a **loose** comparison, so `"false"`, `0`,
    `""` and `1` all pass. Use `in_array(..., [...], true)` plus an `is_bool`/`is_null` check.

12. **`php/Gateways/NevoboGateway.php`** — several issues remain, all in the Nevobo integration:
    - `:478` — the month lookup validates with `strtolower($month)` but then indexes with the
      original `$month` → undefined key for a capitalised month name.
    - `:499` — `createFromFormat('d F Y H:i', …)`: `d` requires two digits, so "1 september" fails
      → `false` returned from a `?DateTime` function → `TypeError`.
    - `:356` — `throw new InvalidArgumentException("… '$team'")` interpolates a `Team` object →
      `Error` (and the message was meant to be `$team->naam`).
    - `:199` — `die("Error: Unable to download the Excel file.")` aborts the request with a
      plain-text body, bypassing the error middleware.
    - `:223-281` — unguarded `preg_match` results, `strpos(null, …)`, `array_combine()` that may
      return `false`, and an O(n²) loop that rewrites `$teams` while iterating it.
    - `:63` — `$_ENV['USEMOCKDATA']` is read without an `isset()`.
    **I did not touch this file:** the Nevobo feed format is not verifiable from the repository
    (only the two mock RSS files under `php/mocks/` are), and a wrong guess would silently corrupt
    the match schedule.

13. **`php/Gateways/TelFluitGateway.php` (`GetTellers`)** — the query has **no `ORDER BY`**, but the
    PHP groups the rows by team by comparing the team name of consecutive rows. Without a stable
    order a team can be split over several groups. *Recommended:* add
    `ORDER BY P.post_title` (or group in SQL).

14. **`php/UseCases/WeekOverzicht/ExcelExport.php`** — `CheckIfNevoboDataIsComplete()` (L234-242) is
    written but **never called**, so `CreateWedstrijdSchema()` dereferences
    `$wedstrijd->timestamp->getTimestamp()` without a null check. Also `disconnectWorksheets()` is
    never called and `ob_end_clean()`/`header()` are used after output has been written
    ("headers already sent"); `Access-Control-Expose-Headers` is missing so the browser cannot
    read the file name.

### 2.3 Design / refactoring

15. **Composer autoloading is classmap-only.** `composer.json` has no `psr-4`, so a new or renamed
    class needs `composer dump-autoload`, and — as the `GateWays` typo showed — a wrong namespace
    casing produces an opaque "class not found" at runtime. **Recommended:** switch to
    `psr-4` (`"TeamPortal\\": ""`) with one namespace per folder, or at least to
    `psr-4` + `classmap`.

16. **`php/RouteObjects/CrudRoute.php:39-73`** — authorisation is a `switch` over magic integers
    (`0`…`5`) that duplicates `AuthorizationRole`, plus `case null;` falling through to `case 0`.
    **Recommended:** a `match` on the `AuthorizationRole` constants, and an
    `AuthorizationRole` enum instead of a class of ints.

17. **`php/Common/Database.php`** — `PDO::ATTR_EMULATE_PREPARES` is not disabled, so MySQL INT
    columns are returned as PHP **strings**. This is the root cause of the strict-comparison bugs
    in the Angular code (fixed there, surgically). Enabling native prepares would fix the whole
    class of problems but changes the JSON types of every `id` in every response — worth doing
    deliberately, in one step, together with a frontend pass.

18. **`php/Common/Database.php`** — no transaction support: the PDO handle is private and
    `getDbConnection()` is private, so **no gateway can start a transaction**. `SwapGateway::AcceptSwaps()`
    performs two `UPDATE`s that must both succeed. **Recommended:** expose
    `beginTransaction()` / `commit()` / `rollBack()` on `Database`.

19. **`php/Entities/Email/Email.php:47-51`** — the dedupe signature is a SHA-1 over
    sender+receiver+title+body. Any whitespace change produces a new signature. Combined with
    issue 8 this needs a deliberate key.

20. **Duplicated logic**
    - The "load a list, set `loading`, handle 500-only errors" block is copy-pasted in five
      components (`tel-fluit-beschikbaarheid`, `barcie-beschikbaarheid`, `wedstrijd-overzicht`,
      `teamtakenco`, `mijn-overzicht`). A shared base component or a small `LoadingState` helper
      would remove it. In all of them the error handler only covers HTTP 500 → **on a 401/403/404
      or a network error the spinner keeps running forever and no message is shown.**
    - `getShortNotation()` exists in three places with three different implementations:
      `php/Entities/Team.php:44`, `src/app/models/Team.ts:4` and
      `src/app/teamtakenco/teamtakenco/teamtakenco.component.ts:78`.
    - `php/Gateways/WordPressGateway.php` and `php/Gateways/BarcieGateway.php` build SQL by
      concatenating `$_ENV['DBNAME']` / `$_ENV['WPDBNAME']` in ~40 places. **Recommended:** a
      `Database::Table(string $name)` helper.
    - `src/app/teamtakenco/selecteer-*.component.ts` pass their parameters via static fields on the
      component class — a shared `MAT_DIALOG_DATA`-style injection would be safer.

21. **Dead code**
    - `src/app/barco/barcie-indeling/barcie-indeling.component.scss` — an orphan duplicate folder
      (the live component is `src/app/barcie/barcie-indeling/`); no module, no imports.
    - `src/app/core/services/calenderGenerator.ts` (~170 lines) — the only call sites are
      commented out, and `this.user` is never assigned inside it.
    - `src/app/models/Bardienst.ts`, `models/NoTeam.ts`, `models/Zaalwacht.ts` — never imported.
      (`models/Speeldag.ts` declares `bardiensten: Bardienst;` where it should be `Bardienst[]`.)
    - `src/app/core/core.module.ts` — declares nothing; the interceptors are registered in `AppModule`.
    - `php/UseCases/Teamtakenco/TeamtakencoFunctions.php` — an empty file.
    - `php/UseCases/Models/TeamoverzichtModel.php`, `CoachModel.php`, `TelteamModel.php` — never
      instantiated, and `TeamoverzichtModel` reads five properties (`uitslagen`, `stand`,
      `programma`, `trainers`, `coaches`) that do not exist on `Team`.
    - `php/UseCases/Statistics/GetSkcRanking.php:27-36` — `skc-teams.json` is loaded and validated
      into `$teams`, which is then never used.
    - `src/app/mijn-overzicht/mijn-overzicht/mijn-overzicht.component.ts:111-162` — ~50 lines of
      commented-out swap code.

22. **Small UI/logic issues**
    - `src/app/shared/tellers/tellers.component.html:2` — debug text shipped in the UI:
      `<span class="badge" …><h1>Bruh</h1>`.
    - `src/app/teamtakenco/exportascsv/exportascsv.component.ts:28-45` — the blob response is
      discarded and a *second* request is triggered through an `<a href>`; `a.download = 'example.csv'`
      although the export is Excel; `window.URL.revokeObjectURL(url)` is called on a non-blob URL.
    - `src/app/core/services/emails.service.ts:18-26` — the list is cached forever and never
      invalidated → the Emails page does not show newly queued mails until a full page reload.
    - `src/app/core/services/request.service.ts:67-70` — `GetCurrentUser()` is typed
      `Observable<any[]>` but returns a single object; `responseType: 'blob' as 'json'` (L72-80).
    - `src/app/route.config.ts` — the route path `'Barcie'` is capitalised while every other route
      is kebab-case; the `wedstrijd-overzicht` route has `isHidden: true` and no `data.groups`, so
      `ShowMenuItems()` can never un-hide it → that page is reachable by URL only.
    - `src/app/mijn-overzicht/ruil-lijst/ruil-lijst.component.html:85` — `task.persoon.achternaam`
      does not exist in the API (`Persoon` exposes `naam`, `voornaam`, `afkorting`) → the column is
      always blank.

### 2.4 Infrastructure / process

23. **The dev container cannot reproduce production.** `docker/php/Dockerfile` is
    `FROM joomla:3.10.11` (PHP 7.4) and `docker/wordpress/docker-compose.yml` uses
    `wordpress:php7.4`, while production runs PHP 8.5. Every PHP 8.x issue above was therefore
    invisible in development. **Recommended:** pin the dev image to `php:8.5-apache`.

24. `docker/docker-compose.yml` still uses the Joomla environment variables (`JOOMLA_DB_HOST`,
    `JOOMLA_DB_PASSWORD`) instead of `DBHOSTNAME` / `DBNAME` / `DBUSERNAME` / `DBPASSWORD`, and
    mounts `./php/www-root`, a directory that does not exist in the repository (it is gitignored).

25. `docker/php/Dockerfile` enables xdebug with `xdebug.mode=debug` and
    `xdebug.start_with_request=yes` → every request is a full debug session. `xdebug.mode=develop`
    with `start_with_request=trigger` would be enough.

26. **CI runs no tests.** `.github/workflows/master.yml:44-45` has the PHPUnit step commented out,
    `ng test` is never invoked, and `node-version: 16` is pinned while Angular 15 wants ≥16.14.
    The four `*.spec.ts` files also fail as written (missing `HttpClientTestingModule` and
    `ActivatedRoute`/`RouterModule` providers), so `ng test` would be red until they are fixed.
    **Recommended:** fix the four specs, then add `ng test --watch=false --browsers=ChromeHeadless`
    and `vendor/bin/phpunit` to the workflow.

27. `deploy.sh` is out of sync with the app: it copies a `configuration.php` (the app uses `.env`
    via `vlucas/phpdotenv`), removes a `./php/errors` directory that does not exist, and runs
    `npm -f` (not a real npm flag).

28. `php/.phpunit.result.cache` is committed to the repository.

29. `error_reporting(E_ALL ^ E_DEPRECATED)` at file scope in five use cases
    (`MijnOverzicht/GetMijnOverzicht.php:14`, `WeekOverzicht/GetWeekOverzicht.php:20`,
    `WedstrijdOverzicht/GetWedstrijdOverzicht.php:15` and all five `Swap/*.php`) disables
    deprecation reporting for the **whole request** — which hides exactly the 8.2/8.4/8.5 issues
    listed above. The comment even says *"Make sure to fix the usort() functions in this file for
    PHP 8.1"*. **Recommended:** remove those lines now that the comparators are fixed.

30. `php/TODO` still lists open items, e.g. *"fluit en bar beschikbaarheden opslaan niet als
    ja/nee, maar ja/nee/onbekend"* — note that `barcie_availability.is_beschikbaar` is
    `ENUM('Ja','Nee')` and cannot store "Onbekend", while `TeamPortal_fluitbeschikbaarheid` can.
    Also *"tellers klopt niet in agenda"* relates to `GetCalendar`, which was broken by the
    `Team`-to-string bug (now fixed) — worth re-testing.

---

## 3. Suggested order of work

1. Re-run the test suite on PHP 8.5 (`composer install && vendor/bin/phpunit`) — the PHP changes in
   this set were **not** executed.
2. Decide on the `/tasks/*` and `/swaps/*` authentication model (issue 1) — it is the highest-risk
   finding and needs a product/security decision.
3. Move the dev Docker image to PHP 8.5 and add `ng test` + PHPUnit to CI, so the remaining
   findings cannot silently rot again.
4. Work through the Nevobo integration (issue 12) with a real feed sample.
5. Decide on the PDO native-prepares change (issue 17) as one coordinated step.
