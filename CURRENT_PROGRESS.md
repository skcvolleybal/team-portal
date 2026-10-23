# Migration Progress Report — team-portal (Angular 15 → 22, Node 16 → 24)

**Branch:** `migration/angular-22-node-24`
**Working Directory:** `/home/niels/SKC/starthier/team-portal`
**Source:** MIGRATION.md (phases 1-13)

---

## 📊 CURRENT STATUS: Phase 7 — RxJS 6 → 7 Migration [COMPLETE]

Phase 7 is **COMPLETE** - all 4 interceptors updated from `rxjs/internal/Observable` to `rxjs`.
`ng build` passes successfully with only warnings (no errors).

---

## ✅ COMPLETED PHASES

### Phase 1: Preparation

| Task                                             | Status                                        |
| ------------------------------------------------ | --------------------------------------------- |
| Create git branch `migration/angular-22-node-24` | ✅ Done                                       |
| Node 24 installation (local v24.22.1)            | ✅ Already installed                          |
| Verify current build                             | ✅ Passing                                    |
| Read UPDATE.md                                   | ✅ Read — user said to ignore, work from repo |

### Phase 2: Package.json & Dependencies

| Package                                   | Old               | New          | Status  |
| ----------------------------------------- | ----------------- | ------------ | ------- |
| `@angular/*` (all)                        | ^15.2.x           | ^22.2.0      | ✅ Done |
| `@ng-bootstrap/ng-bootstrap`              | ^14.2.0           | ^21.0.0      | ✅ Done |
| `ngx-toastr`                              | ^16.2.0           | ^20.0.5      | ✅ Done |
| `@fortawesome/*` icons                    | ^5.15.4 / ~1.2.36 | ^7.3.1       | ✅ Done |
| `@fortawesome/angular-fontawesome`        | ^0.12.1           | ^5.1.0       | ✅ Done |
| `bootstrap`                               | ^5.3.2            | ^5.3.8       | ✅ Done |
| `chart.js`                                | ^2.9.4            | ^4.5.1       | ✅ Done |
| `ical.js`                                 | ^1.5.0            | ^2.2.1       | ✅ Done |
| `ical`                                    | —                 | ^0.8.0       | ✅ Done |
| `rxjs`                                    | ^6.6.3            | ~7.8.0       | ✅ Done |
| `zone.js`                                 | ~0.11.4           | ~0.16.0      | ✅ Done |
| `tslib`                                   | ^2.0.3            | ^2.8.0       | ✅ Done |
| `core-js`                                 | ^2.6.12           | Removed      | ✅ Done |
| `protractor`                              | ~7.0.0            | Removed      | ✅ Done |
| TypeScript                                | ^4.9.5            | ~6.0.0       | ✅ Done |
| Angular CLI / DevKit / Compiler           | ^15.2.9           | ^22.2.0      | ✅ Done |
| ESLint, angular-eslint, typescript-eslint | —                 | Added        | ✅ Done |
| Karma, Jasmine, jasmine-core              | Old               | Updated      | ✅ Done |
| `npm install`                             | —                 | 910 packages | ✅ Done |
| `e2e` script removed from package.json    | —                 | Removed      | ✅ Done |

### Phase 3: Angular Configuration Files

| File                     | Changes                                                                                                                                                                                                                                                                            | Status  |
| ------------------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------- |
| `angular.json`           | Replaced `@angular-devkit/build-angular:browser` → `@angular/build:application`; `dev-server` → `@angular/build:dev-server`; `karma` → `@angular/build:karma`; removed `team-portal-e2e` project; `main` → `browser`; `polyfills` → array; removed `tsConfig`; added `lint` target | ✅ Done |
| `tsconfig.json`          | `moduleResolution: "bundler"`; `strict: true`; `noImplicitOverride`; `noPropertyAccessFromIndexSignature`; `noFallthroughCasesInSwitch`; target ES2022; added `skipLibCheck: true`                                                                                                 | ✅ Done |
| `src/tsconfig.app.json`  | Updated extends path; fixed include glob                                                                                                                                                                                                                                           | ✅ Done |
| `src/tsconfig.spec.json` | Updated extends path                                                                                                                                                                                                                                                               | ✅ Done |
| `e2e/tsconfig.e2e.json`  | Deleted                                                                                                                                                                                                                                                                            | ✅ Done |

### Phase 4: Bootstrap & Module System — The Biggest Change

| File                       | Changes                                                                                                                                                                                                             | Status  |
| -------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------- |
| `src/main.ts`              | Replaced `platformBrowserDynamic().bootstrapModule(AppModule)` with `bootstrapApplication(AppComponent, appConfig)`                                                                                                 | ✅ Done |
| `src/app/app.config.ts`    | Created with `provideHttpClient(withInterceptorsFromDi())`, `provideAnimations()`, `provideRouter(appRoutes)`, `importProvidersFrom` for BrowserModule, NgbModule, FontAwesomeModule, MatDialogModule, ToastrModule | ✅ Done |
| `src/app/app.module.ts`    | Deleted                                                                                                                                                                                                             | ✅ Done |
| `src/app/app.component.ts` | `standalone: true`; imports: CommonModule, FormsModule, RouterLink, RouterLinkActive, RouterOutlet, NgbModule, FontAwesomeModule, LoginModalComponent                                                               | ✅ Done |
| All 27+ components         | All converted to `standalone: true` with appropriate imports arrays                                                                                                                                                 | ✅ Done |
| All 7 NgModule files       | All deleted (barcie, beschikbaarheid, core, shared, teamtakenco, mijn-overzicht, wedstrijd-overzicht)                                                                                                               | ✅ Done |

### Phase 5: Polyfills

| File               | Changes                                                                    | Status  |
| ------------------ | -------------------------------------------------------------------------- | ------- |
| `src/polyfills.ts` | Removed `core-js/es7/reflect`; kept `zone.js` and `@angular/localize/init` | ✅ Done |

### Phase 6: Template Syntax Updates (Control Flow)

All templates converted to block-level `@if`/`@for`/`@switch` syntax.

| #   | File                                                                                         | Status  |
| --- | -------------------------------------------------------------------------------------------- | ------- |
| 1   | `src/app/app.component.html`                                                                 | ✅ Done |
| 2   | `src/app/statistieken/statistieken.component.html`                                           | ✅ Done |
| 3   | `src/app/emails/emails.component.html`                                                       | ✅ Done |
| 4   | `src/app/email-detail/email-detail.component.html`                                           | ✅ Done |
| 5   | `src/app/barcie/barcie-indeling/barcie-indeling.component.html`                              | ✅ Done |
| 6   | `src/app/barcie/selecteer-barcie-lid/selecteer-barcie-lid.component.html`                    | ✅ Done |
| 7   | `src/app/beschikbaarheid/barcie-beschikbaarheid/barcie-beschikbaarheid.component.html`       | ✅ Done |
| 8   | `src/app/beschikbaarheid/tel-fluit-beschikbaarheid/tel-fluit-beschikbaarheid.component.html` | ✅ Done |
| 9   | `src/app/mijn-overzicht/mijn-overzicht/mijn-overzicht.component.html`                        | ✅ Done |
| 10  | `src/app/mijn-overzicht/ruil-lijst/ruil-lijst.component.html`                                | ✅ Done |
| 11  | `src/app/wedstrijd-overzicht/wedstrijd-overzicht/wedstrijd-overzicht.component.html`         | ✅ Done |
| 12  | `src/app/wedstrijd-overzicht/inval-teams/inval-teams.component.html`                         | ✅ Done |
| 13  | `src/app/wedstrijd-overzicht/spelers-lijst/spelers-lijst.component.html`                     | ✅ Done |
| 14  | `src/app/teamtakenco/teamtakenco/teamtakenco.component.html`                                 | ✅ Done |
| 15  | `src/app/teamtakenco/selecteer-scheidsrechter/selecteer-scheidsrechter.component.html`       | ✅ Done |
| 16  | `src/app/teamtakenco/selecteer-tellers/selecteer-tellers.component.html`                     | ✅ Done |
| 17  | `src/app/teamtakenco/selecteer-zaalwacht/selecteer-zaalwacht.component.html`                 | ✅ Done |
| 18  | `src/app/shared/keuze/keuze.component.html`                                                  | ✅ Done |
| 19  | `src/app/shared/scheidsrechter/scheidsrechter.component.html`                                | ✅ Done |
| 20  | `src/app/shared/spinner/spinner.component.html`                                              | ✅ Done |
| 21  | `src/app/shared/tellers/tellers.component.html`                                              | ✅ Done |
| 22  | `src/app/shared/wedstrijd/wedstrijd.component.html`                                          | ✅ Done |
| 23  | `src/app/shared/wedstrijden-card/wedstrijden-card.component.html`                            | ✅ Done |
| 24  | `src/app/login-modal/login-modal.component.html`                                             | ✅ Done |

### Phase 7: RxJS 6 → 7 Migration

| File                                                         | Change                              | Status  |
| ------------------------------------------------------------ | ----------------------------------- | ------- |
| `src/app/core/interceptors/add-credentials.interceptor.ts`   | `rxjs/internal/Observable` → `rxjs` | ✅ Done |
| `src/app/core/interceptors/add-impersonation.interceptor.ts` | `rxjs/internal/Observable` → `rxjs` | ✅ Done |
| `src/app/core/interceptors/default-headers.interceptor.ts`   | `rxjs/internal/Observable` → `rxjs` | ✅ Done |
| `src/app/core/interceptors/is-authorized.interceptor.ts`     | `rxjs/internal/Observable` → `rxjs` | ✅ Done |

---

## 🔧 BUILD STATUS

`ng build --configuration production` — **PASSES** ✅

Only non-blocking warnings remain:

- ical.js import warnings (Component, Time, Duration) — addressed with `skipLibCheck: true`
- NG8113 unused imports (8 components) — cosmetic only
- Sass `@import` deprecation in `src/styles.scss`

---

## ⏳ NOT STARTED: Remaining Phases

### Phase 8: FontAwesome 5 → 7 Migration

- Audit icon names in all component templates and TS files
- Icon names that may have changed: faAngleRight, faCheckCircle, faCogs, faExclamationCircle, faCoffee, faExchangeAlt, faCalendarCheck, faPlusSquare, faUser, faInfoCircle, faPeopleCarry, faCalendar, faTrashAlt, faUserTie, faCalendarAlt, faTrash, faAmbulance, faTimes, faCheck, faClose

### Phase 9: Chart.js 2 → 4 Migration

- Check if chart.js is actually used (grep found no usage in components)
- If used, migrate to new Chart.js 4 API

### Phase 10: ESLint Setup

- Create `eslint.config.js` (flat config format for ESLint 10)
- Delete `tslint.json` (root) and `src/tslint.json`
- `angular.json` lint target already added ✅
- `lint` script already in package.json ✅

### Phase 11: Testing

- Update `src/karma.conf.js` for ChromeHeadless
- Delete `e2e/protractor.conf.js`, `e2e/conf.js`, `e2e/` directory
- `src/test.ts` may need minimal updates

### Phase 12: Docker & CI/CD

| File                           | Change                                                                         |
| ------------------------------ | ------------------------------------------------------------------------------ |
| `Dockerfile-frontend`          | `node:16-alpine` → `node:24-alpine`; `@angular/cli@15.2.9` → `@angular/cli@22` |
| `.github/workflows/dev.yml`    | `node-version: 16` → `24`                                                      |
| `.github/workflows/master.yml` | `node-version: 16` → `24`                                                      |
| `docker/docker-compose.yml`    | PHP images PHP 7.4 → PHP 8.5                                                   |

### Phase 13: Specific Code Fixes from REVIEW.md

| File                                                     | Issue                                            | Fix                                      |
| -------------------------------------------------------- | ------------------------------------------------ | ---------------------------------------- |
| `teamtakenco/selecteer-scheidsrechter/component.html:15` | `index as i` removed                             | `let i = index`                          |
| `barcie/barcie-indeling/component.html:11`               | `(ngModelChange)` on formControlName             | `(dateChange)`                           |
| `mijn-overzicht/component.html:37,80,81,94`              | `user.team.naam` deref on undefined              | `user && …` guards                       |
| `mijn-overzicht/component.ts:94`                         | `"42" === 42` false                              | `Number(...) === Number(...)`            |
| `app.component.ts:67-73`                                 | Multiple login modals on 401                     | `loginModalOpen` guard (already in code) |
| `statistieken/component.html:40`                         | `(sort)="onSort($event)"` — method doesn't exist | Remove binding                           |
| `barcie/barcie-indeling/component.ts:82`                 | `barciedag.shifts[shift - 1]` positional         | Look up by shift number                  |

---

## 🔴 IMMEDIATE NEXT STEPS (in order)

1. **Phase 8: FontAwesome 5 → 7 Migration** — Audit and fix icon names
2. **Phase 9: Chart.js 2 → 4 Migration** — Check usage and migrate if needed
3. **Phase 10: ESLint Setup** — Create eslint.config.js, delete tslint files
4. **Phase 11: Testing** — Update karma.conf.js, delete e2e directory
5. **Phase 12: Docker & CI/CD** — Update Dockerfile, workflows, docker-compose
6. **Phase 13: REVIEW.md bug fixes** — Apply specific code fixes
7. **Run validation** (`npm install`, `ng lint`, `ng build`, `ng test`)

---

## 📁 KEY FILES MODIFIED SO FAR

### Configuration

- `package.json` — Rewritten to Angular 22.x
- `angular.json` — Restructured for `@angular/build:application`
- `tsconfig.json` — Modernized with strict mode; added `skipLibCheck: true`
- `src/tsconfig.app.json` — Updated
- `src/tsconfig.spec.json` — Updated
- `src/polyfills.ts` — Removed core-js

### Bootstrap & App

- `src/main.ts` — `bootstrapApplication(AppComponent, appConfig)`
- `src/app/app.config.ts` — Created (new file)
- `src/app/app.component.ts` — Standalone; fixed index signature, type annotations, null assignment, searchUsers type
- `src/app/app.module.ts` — Deleted

### Interceptors (Phase 7)

- `src/app/core/interceptors/add-credentials.interceptor.ts` — RxJS import fix
- `src/app/core/interceptors/add-impersonation.interceptor.ts` — RxJS import fix
- `src/app/core/interceptors/default-headers.interceptor.ts` — RxJS import fix
- `src/app/core/interceptors/is-authorized.interceptor.ts` — RxJS import fix

### Deleted

- `src/app/app.module.ts`
- `src/app/barcie/barcie.module.ts`
- `src/app/beschikbaarheid/beschikbaarheid.module.ts`
- `src/app/core/core.module.ts`
- `src/app/shared/shared.module.ts`
- `src/app/teamtakenco/teamtakenco.module.ts`
- `src/app/mijn-overzicht/mijn-overzicht.module.ts`
- `src/app/wedstrijd-overzicht/wedstrijd-overzicht.module.ts`
- `e2e/tsconfig.e2e.json`

---

## 🚨 KNOWN ISSUES

1. **`src/environments/version.ts`** — Generated by CI workflow; may need to exist locally for builds to work. `app.component.ts` imports `version` from `../environments/version`.
2. **`src/app/models/Task.ts`** — Referenced by `ruil-lijst.component.ts` as `import { Task } from "./Task"`; need to verify this file exists.
3. **ical.js type incompatibility** — Addressed with `skipLibCheck: true` in tsconfig.json.
4. **Unused import warnings** — NG8113 warnings for 8 components are cosmetic only, not blocking.

---

## ✍️ Migration Guide Reference

Source: `MIGRATION.md` at `/home/niels/SKC/starthier/team-portal/MIGRATION.md`

Plan phases (13 total):

1. Preparation ✅
2. Package.json & Dependencies ✅
3. Angular Configuration Files ✅
4. Bootstrap & Module System ✅
5. Polyfills ✅
6. Template Syntax Updates (Control Flow) ✅
7. RxJS 6 → 7 Migration ✅
8. FontAwesome 5 → 7 Migration ⏳
9. Chart.js 2 → 4 Migration ⏳
10. ESLint Setup ⏳
11. Testing ⏳
12. Docker & CI/CD ⏳
13. Specific Code Fixes from REVIEW.md ⏳
