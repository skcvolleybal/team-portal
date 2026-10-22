# Migration Plan: team-portal — Node 16 + Angular 15 → Node 24 + Angular 22

## 1. Executive Summary

The `team-portal` is an Angular 15.2.9 frontend application (Node 16) that serves as a team task coordinator for SKC volleyball club. It communicates with a PHP/WordPress backend via HTTP REST APIs. The application uses NgModules (not standalone components), TSLint, Protractor for e2e, Karma/Jasmine for unit tests, and is deployed via Docker.

**Current state:** Angular 15.2.9, TypeScript 4.9.5, RxJS 6.6.3, Zone.js 0.11.4, Node 16, TSLint, Protractor, NgModules-based architecture.

**Target state:** Angular 22.x, TypeScript ~6.0, RxJS 7.8+, Zone.js 0.16.x (optional), Node 24, ESLint, standalone components, `provideHttpClient`/`provideRouter`/`bootstrapApplication`.

This is a **7-version major jump** (Angular 15 → 22). It cannot be done in a single `ng update`; it requires staged upgrades through Angular 16, 17, 18, 19, 20, 21, 22, or a carefully planned direct jump with manual code changes.

---

## 2. Current State — Complete File Inventory

### 2.1 Configuration Files

| File | Current Version | Status |
|------|----------------|--------|
| `package.json` | Angular 15.2.9, Node 16 | Needs full rewrite |
| `angular.json` | Angular CLI 15 schema | Needs `@angular/build:application` migration |
| `tsconfig.json` | Target ES2022, module ES2020 | Needs moduleResolution bundler |
| `src/tsconfig.app.json` | Extends root | Needs updates |
| `src/tsconfig.spec.json` | Extends root | Needs updates |
| `e2e/tsconfig.e2e.json` | Target ES5 | Needs removal/rewrite |
| `tslint.json` (root + src/) | TSLint | **Delete**, replace with ESLint |
| `karma.conf.js` | Karma 6.3, Chrome | Needs ChromeHeadless, updated plugins |
| `protractor.conf.js` | Protractor 7.0 | **Remove** (Protractor is deprecated/removed) |
| `conf.js` | Protractor config | **Remove** |
| `Dockerfile-frontend` | `node:16-alpine` | → `node:24-alpine` |
| `docker/docker-compose.yml` | PHP 7.4 Joomla | Needs PHP 8.5 alignment |
| `deploy.sh` | SSH deployment | May need updates |

### 2.2 Source Files — Components (27+ components)

**Root module components:**
- `src/app/app.component.ts` + `.html` + `.css` — Main nav, login modal trigger, route filtering by groups
- `src/app/app.module.ts` — NgModule with all imports, providers, interceptors
- `src/app/route.config.ts` — Route definitions with `isHidden` extension, groups-based authorization
- `src/app/login-modal/login-modal.component.*` — Login form, WordPress auth

**Feature modules:**
| Module | Components |
|--------|-----------|
| `barcie/` | `barcie-indeling/`, `selecteer-barcie-lid/` |
| `beschikbaarheid/` | `barcie-beschikbaarheid/`, `tel-fluit-beschikbaarheid/` |
| `mijn-overzicht/` | `mijn-overzicht/`, `ruil-lijst/` |
| `statistieken/` | `statistieken.component.*` |
| `emails/` | `emails.component.*` |
| `email-detail/` | `email-detail.component.*` |
| `teamtakenco/` | `teamtakenco/`, `exportascsv/`, `selecteer-scheidsrechter/`, `selecteer-tellers/`, `selecteer-zaalwacht/` |
| `wedstrijd-overzicht/` | `wedstrijd-overzicht/`, `inval-teams/`, `spelers-lijst/` |
| `shared/` | `keuze/`, `scheidsrechter/`, `spinner/`, `tellers/`, `wedstrijd/`, `wedstrijden-card/` |

### 2.3 Source Files — Services (10 services)

| File | Purpose |
|------|---------|
| `src/app/core/services/request.service.ts` | WordPress API calls (groups, users, login, overzicht, diensten) |
| `src/app/core/services/state.service.ts` | Auth state, webcie flag, impersonation ID |
| `src/app/core/services/beschikbaarheid.service.ts` | Fluit/barcie availability updates |
| `src/app/core/services/barcie.service.ts` | Barcie dag/rooster management |
| `src/app/core/services/teamtakenco.service.ts` | Team task coordinator (referees, tellers, zaalwacht) |
| `src/app/core/services/aanwezigheid.service.ts` | Attendance update/delete |
| `src/app/core/services/statistics.service.ts` | SKC ranking data |
| `src/app/core/services/emails.service.ts` | Email listing with caching |
| `src/app/core/services/calenderGenerator.ts` | iCal generation (mostly commented out) |
| `src/app/core/services/swap.service.ts` | Swap proposals (referenced in ruil-lijst) |
| `src/app/core/services/notifications.service.ts` | Toastr notifications (referenced in ruil-lijst) |

### 2.4 Source Files — Interceptors (4)

| File | Purpose |
|------|---------|
| `src/app/core/interceptors/default-headers.interceptor.ts` | Sets Content-Type: application/json |
| `src/app/core/interceptors/is-authorized.interceptor.ts` | HTTPListener — sets auth state on 401 |
| `src/app/core/interceptors/add-impersonation.interceptor.ts` | Adds impersonationId param for webcie |
| `src/app/core/interceptors/add-credentials.interceptor.ts` | Sets withCredentials: true |

### 2.5 Source Files — Models (9)

| File | Status |
|------|--------|
| `src/app/models/Email.ts` | Used by email-detail |
| `src/app/models/Persoon.ts` | Used by Wedstrijd |
| `src/app/models/Team.ts` | Base class, getShortNotation() |
| `src/app/models/NoTeam.ts` | Extends Team — **never imported** |
| `src/app/models/Wedstrijd.ts` | Match data |
| `src/app/models/Speeldag.ts` | References Bardienst (should be Bardienst[]) |
| `src/app/models/Speeltijd.ts` | Time slot with wedstrijden |
| `src/app/models/Bardienst.ts` | **Never imported** |
| `src/app/models/Zaalwacht.ts` | **Never imported** |

### 2.6 Environment Files

| File | Content |
|------|---------|
| `src/environments/environment.ts` | `production: false`, `baseUrl: 'http://localhost/team-portal/api/'` |
| `src/environments/environment.prod.ts` | `production: true`, production baseUrl |
| `src/environments/environment.test_deploy.ts` | Test deploy baseUrl |
| `src/environments/version.ts` | git branch/hash info |

### 2.7 Other Files

| File | Purpose |
|------|---------|
| `src/main.ts` | `platformBrowserDynamic().bootstrapModule(AppModule)` |
| `src/polyfills.ts` | core-js/es7/reflect, zone.js, @angular/localize/init |
| `src/index.html` | Basic HTML with Roboto fonts, Material Icons |
| `src/styles.scss` | Material theme, Bootstrap, toastr, custom styles |
| `src/test.ts` | Test bed initialization |
| `src/favicon.ico`, `src/favicon.png`, `src/assets/skc_logo.png` | Static assets |
| `e2e/src/app.po.ts` | Protractor page object |
| `README.md` | Project description |
| `REVIEW.md` | Code review findings (bugs fixed + pending) |

---

## 3. Dependency Changes

### 3.1 Dependencies to Update (from package.json)

| Package | Current | Target | Notes |
|---------|---------|--------|-------|
| `@angular/animations` | ^15.2.10 | ^22.2.0 | Deprecated — use `provideAnimations()` |
| `@angular/cdk` | ^15.2.9 | ^22.2.0 | |
| `@angular/common` | ^15.2.9 | ^22.2.0 | |
| `@angular/compiler` | ^15.2.9 | ^22.2.0 | |
| `@angular/core` | ^15.2.9 | ^22.2.0 | |
| `@angular/forms` | ^15.2.9 | ^22.2.0 | |
| `@angular/localize` | ^15.2.9 | ^22.2.0 | |
| `@angular/material` | ^15.2.9 | ^22.2.0 | |
| `@angular/platform-browser` | ^15.2.9 | ^22.2.0 | |
| `@angular/platform-browser-dynamic` | ^15.2.9 | ^22.2.0 | |
| `@angular/router` | ^15.2.9 | ^22.2.0 | |
| `@fortawesome/angular-fontawesome` | ^0.12.1 | ^5.1.0 | Peers Angular 22 |
| `@fortawesome/fontawesome-svg-core` | ~1.2.36 | ^7.3.1 | Icon names may change (v5→v7) |
| `@fortawesome/free-brands-svg-icons` | ^5.15.4 | ^7.3.1 | |
| `@fortawesome/free-regular-svg-icons` | ^5.15.4 | ^7.3.1 | |
| `@fortawesome/free-solid-svg-icons` | ^5.15.4 | ^7.3.1 | |
| `@ng-bootstrap/ng-bootstrap` | ^14.2.0 | ^21.0.0 | Peers Angular 22 |
| `@popperjs/core` | ^2.11.8 | Keep/Update | |
| `bootstrap` | ^5.3.2 | ^5.3.8 | |
| `chart.js` | ^2.9.4 | ^4.5.1 | **Breaking API changes** — code migration needed |
| `core-js` | ^2.6.12 | Remove | No longer needed |
| `ical` | ^0.8.0 | Keep | |
| `ical.js` | ^1.5.0 | ^2.2.1 | calenderGenerator.ts migration |
| `ngx-toastr` | ^16.2.0 | ^19.1.0 | Compatible with Angular 22 |
| `protractor` | ~7.0.0 | **Remove** | Deprecated, replaced by Playwright/Cypress |
| `rxjs` | ^6.6.3 | ~7.8.0 | Breaking changes (import paths) |
| `tslib` | ^2.0.3 | ^2.8.0 | |
| `zone.js` | ~0.11.4 | ~0.16.0 | Optional in Angular 22 |

### 3.2 DevDependencies to Update

| Package | Current | Target |
|---------|---------|--------|
| `@angular-devkit/build-angular` | ^15.2.9 | ^22.2.0 (or `@angular/build`) |
| `@angular/cli` | ^15.2.9 | ^22.2.0 |
| `@angular/compiler-cli` | ^15.2.9 | ^22.2.0 |
| `@angular/language-service` | ^15.2.9 | ^22.2.0 |
| `@types/jasmine` | ~3.6.0 | ^5.1.0 |
| `@types/jasminewd2` | ^2.0.8 | Remove |
| `@types/node` | ^12.19.8 | ^26.0.0 |
| `karma` | ~6.3.20 | ^6.4.0 |
| `karma-chrome-launcher` | ~3.1.0 | Keep/Update |
| `karma-coverage-istanbul-reporter` | ~3.0.2 | → `karma-coverage` ^2.2.0 |
| `karma-jasmine` | ~4.0.0 | ^5.1.0 |
| `karma-jasmine-html-reporter` | ^1.5.0 | Keep/Update |
| `ts-node` | ^8.10.2 | Remove (e2e removal) |
| `typescript` | ^4.9.5 | ~6.0.0 |
| `eslint` | — | ^10.13.0 (new) |
| `angular-eslint` | — | ^22.6.0 (new) |
| `typescript-eslint` | — | ^8.72.0 (new) |
| `jasmine-core` | — | ^5.1.0 (new) |

---

## 4. Migration Strategy — Step-by-Step

### Phase 1: Preparation (Before any code changes)

1. **Backup**: Create a git branch `migration/angular-22-node-24`
2. **Node 24 installation**: Install Node 24 LTS locally and in CI
3. **Review current build**: Verify `ng build --configuration production` passes on current code
4. **Read UPDATE.md**: The UPDATE.md already documents partial work done (package.json rewritten to Angular 22). Apply those changes if not already in the working tree.

### Phase 2: Package.json & Dependencies

1. Update all `@angular/*` packages to `^22.2.0`
2. Update `@ng-bootstrap/ng-bootstrap` to `^21.0.0`
3. Update `ngx-toastr` to `^19.1.0`
4. Update `@fortawesome/*` to `^7.3.1` and `@fortawesome/angular-fontawesome` to `^5.1.0`
5. Update `bootstrap` to `^5.3.8`
6. Update `chart.js` to `^4.5.1`
7. Update `ical.js` to `^2.2.1`
8. Update `rxjs` to `~7.8.0`
9. Update `zone.js` to `~0.16.0`
10. Update `tslib` to `^2.8.0`
11. Remove `core-js` and `protractor`
12. Update devDependencies (TypeScript 6.0, Angular CLI 22, etc.)
13. Add ESLint, angular-eslint, typescript-eslint
14. Run `npm install` and regenerate `package-lock.json`

### Phase 3: Angular Configuration Files

#### 3.3.1 `angular.json` — Major restructure

Changes needed:
- Replace `@angular-devkit/build-angular:browser` with `@angular/build:application`
- Replace `@angular-devkit/build-angular:dev-server` with `@angular/build:dev-server`
- Replace `@angular-devkit/build-angular:karma` with `@angular/build:karma`
- Remove `team-portal-e2e` project (Protractor removed)
- Remove `e2e` script from package.json
- Replace `browserTarget` with `buildTarget` in serve config
- Replace `main: "src/main.ts"` with `browser: "src/main.ts"`
- Replace `polyfills: "src/polyfills.ts"` with `polyfills: ["src/polyfills.ts"]` (array)
- Remove `tsConfig` from build options (not used by application builder)
- Add `lint` target with `@angular-eslint/builder:lint`
- Update budgets, fileReplacements, optimization settings

#### 3.3.2 `tsconfig.json` — Modernize

Changes needed:
- `moduleResolution: "bundler"` (instead of `"node"`)
- Add `strict: true` (or enable individual strict flags)
- Add `noImplicitOverride`, `noPropertyAccessFromIndexSignature`, `noFallthroughCasesInSwitch`
- Target ES2022 (already correct)
- Module ES2022 or ESNext
- Remove `typeRoots` restriction if not needed

#### 3.3.3 `src/tsconfig.app.json` and `src/tsconfig.spec.json`

Update `extends` paths and compiler options as needed.

#### 3.3.4 `e2e/tsconfig.e2e.json` — Delete

Protractor is being removed, so this file is no longer needed.

### Phase 4: Bootstrap & Module System — The Biggest Change

#### 4.1 `src/main.ts` — Replace NgModule bootstrap with standalone

**Current:**
```typescript
import { AppModule } from './app/app.module';
import { enableProdMode } from '@angular/core';
import { environment } from './environments/environment';
import { platformBrowserDynamic } from '@angular/platform-browser-dynamic';

if (environment.production) {
  enableProdMode();
}

platformBrowserDynamic()
  .bootstrapModule(AppModule)
  .catch((err) => console.error(err));
```

**Target:**
```typescript
import { bootstrapApplication } from '@angular/platform-browser';
import { AppComponent } from './app/app.component';
import { appConfig } from './app/app.config';
import { enableProdMode } from '@angular/core';
import { environment } from './environments/environment';

if (environment.production) {
  enableProdMode();
}

bootstrapApplication(AppComponent, appConfig)
  .catch((err) => console.error(err));
```

#### 4.2 `src/app/app.module.ts` → `src/app/app.config.ts`

Convert from NgModule to application config:
- Remove `NgModule` decorator, imports array, declarations, providers, bootstrap
- Create `appConfig` with `providers` array:
  - `provideHttpClient(withInterceptorsFromDI())` — replaces HttpClientModule
  - `provideAnimations()` — replaces BrowserAnimationsModule
  - `provideRouter(appRoutes)` — replaces RouterModule.forRoot()
  - All services (already `providedIn: 'root'`, can stay)
  - Interceptors via `provideHTTPInterceptors()` or functional interceptors

**Note:** The 4 existing interceptors are class-based. In Angular 22, you can either:
- Keep them as classes and use `withInterceptorsFromDI()` (still works)
- Convert to functional interceptors (recommended)

#### 4.3 `src/app/app.component.ts` — Make standalone

- Add `standalone: true` to `@Component`
- Add `imports` array with needed modules (RouterOutlet, NgbModule, FontAwesomeModule, etc.)
- Remove `styleUrls`/`templateUrl` references can stay

#### 4.4 All other components — Make standalone

Every component in the project needs:
- `standalone: true`
- `imports` array listing all dependencies (CommonModule, FormsModule, ReactiveFormsModule, NgbModule, FontAwesomeModule, SharedModule components, RouterModule, etc.)
- Remove module declarations from NgModules

#### 4.5 All NgModules — Delete or convert

- `AppModule` → replaced by `app.config.ts`
- `BarcieModule`, `BeschikbaarheidModule`, `MijnOverzichtModule`, `WedstrijdOverzichtModule`, `TeamtakencoModule`, `SharedModule`, `CoreModule` → all deleted, components become standalone

### Phase 5: Polyfills

**Current `src/polyfills.ts`:**
- core-js/es7/reflect (for JIT — can be removed with AOT)
- zone.js
- @angular/localize/init (already in main.ts as import)

**Target:** Keep only `zone.js` (if using Zone.js) or remove zone.js entirely (Angular 22 makes it optional). Keep `@angular/localize/init` in main.ts.

### Phase 6: Template Syntax Updates (Control Flow)

Angular 17+ introduced new control flow syntax. All `*ngIf`, `*ngFor`, `*ngSwitch` should be migrated to `@if`, `@for`, `@switch`.

Examples of files needing changes:
- `src/app/app.component.html` — `*ngFor="let appRoute of appRoutes"`, `*ngIf="isWebcie"`, `*ngIf="!isProd"`
- `src/app/statistieken/statistieken.component.html` — `*ngIf`, `*ngFor`
- `src/app/emails/emails.component.html` — Check template
- `src/app/wedstrijd-overzicht/wedstrijd-overzicht/wedstrijd-overzicht.component.html` — `*ngFor`
- All other component HTML templates

### Phase 7: RxJS 6 → 7 Migration

Key changes:
- `import { Observable } from 'rxjs/internal/Observable'` → `import { Observable } from 'rxjs'`
- `pipe` operators from `rxjs/operators` — same in v7, but ensure no deprecated operators
- `share()` replay behavior changed (default replayCount 0 in v7 vs 1 in v6) — check `GetDienstenForUser` in request.service.ts

### Phase 8: FontAwesome 5 → 7 Migration

Icon names may have changed between v5 and v7. Audit all `<fa-icon [icon]="...">` usages:
- `faAngleRight`, `faCheckCircle`, `faCogs`, `faExclamationCircle`, `faCoffee`
- `faExchangeAlt`, `faCalendarCheck`, `faPlusSquare`, `faUser`, `faInfoCircle`, `faPeopleCarry`, `faCalendar`
- `faTrashAlt`, `faUserTie`, `faCalendarAlt`, `faTrash`, `faAmbulance`, `faTimes`, `faCheck`, `faClose`

### Phase 9: Chart.js 2 → 4 Migration

Check if chart.js is actually used (grep found no chart.js usage in components, but it's in package.json). If used, the API changed significantly (Chart.register, new component names, etc.)

### Phase 10: ESLint Setup

Replace TSLint with ESLint:
- Create `eslint.config.js` (flat config format for ESLint 10)
- Add `angular-eslint` plugin with Angular-specific rules
- Delete `tslint.json` (root and src/)
- Add `lint` script to package.json
- Add lint target to angular.json

### Phase 11: Testing

#### Unit Tests (Karma + Jasmine)
- Update `karma.conf.js` for ChromeHeadless (no GUI in CI)
- Update plugin imports for Angular 22
- `src/test.ts` — may need minimal updates

#### E2E Tests (Protractor → Playwright or Cypress)
- Remove `e2e/` directory, `protractor.conf.js`, `conf.js`
- Add Playwright or Cypress configuration
- Rewrite `e2e/src/app.po.ts` tests

### Phase 12: Docker & CI/CD

#### `Dockerfile-frontend`
- `FROM node:16-alpine` → `FROM node:24-alpine`
- `npm install -g @angular/cli@15.2.9` → `@angular/cli@22`
- `ng serve --host 0.0.0.0 --poll 2000` — keep or update

#### `.github/workflows/dev.yml` (if exists)
- `node-version: 16` → `node-version: 24`
- `actions/checkout@v3` → `v4`
- `setup-node@v3` → `v4`

#### `docker/docker-compose.yml`
- PHP images need updating from Joomla 3.10.11 (PHP 7.4) to PHP 8.5

### Phase 13: Specific Code Fixes from REVIEW.md

The REVIEW.md documents several bugs that should be fixed during migration:

| File | Issue | Fix |
|------|-------|-----|
| `src/app/teamtakenco/selecteer-scheidsrechter/selecteer-scheidsrechter.component.html:15` | `index as i` removed in Angular 5 | `let i = index` |
| `src/app/barcie/barcie-indeling/barcie-indeling.component.html:11` | `(ngModelChange)` on formControlName input | `(dateChange)` |
| `src/app/mijn-overzicht/mijn-overzicht/mijn-overzicht.component.html:37,80,81,94` | `user.team.naam` deref on undefined | `user && …` guards |
| `src/app/mijn-overzicht/mijn-overzicht/mijn-overzicht.component.ts:94` | `"42" === 42` false | `Number(...) === Number(...)` |
| `src/app/app.component.ts:67-73` | Multiple login modals on 401 | `loginModalOpen` guard (already in code) |
| `src/app/statistieken/statistieken.component.html:40` | `(sort)="onSort($event)"` — method doesn't exist | Remove binding |
| `src/app/barcie/barcie-indeling/barcie-indeling.component.ts:82` | `barciedag.shifts[shift - 1]` positional lookup | Look up by shift number |

---

## 5. Detailed File-by-File Change List

### 5.1 Configuration Files (Create/Modify/Delete)

| File | Action | Changes |
|------|--------|---------|
| `package.json` | Modify | All dependency versions updated per section 3 |
| `package-lock.json` | Regenerate | Run `npm install` |
| `angular.json` | Modify | `@angular/build:application`, remove e2e project, add lint target |
| `tsconfig.json` | Modify | moduleResolution bundler, strict flags |
| `src/tsconfig.app.json` | Modify | Update extends/options |
| `src/tsconfig.spec.json` | Modify | Update extends/options |
| `e2e/tsconfig.e2e.json` | **Delete** | No longer needed |
| `tslint.json` | **Delete** | Replace with ESLint |
| `src/tslint.json` | **Delete** | Replace with ESLint |
| `eslint.config.js` | **Create** | ESLint 10 flat config |
| `karma.conf.js` | Modify | ChromeHeadless, updated plugins |
| `protractor.conf.js` | **Delete** | Protractor removed |
| `conf.js` | **Delete** | Protractor config removed |
| `e2e/` directory | **Delete** | Remove e2e tests or rewrite with Playwright |
| `Dockerfile-frontend` | Modify | node:24-alpine, Angular CLI 22 |
| `docker/docker-compose.yml` | Modify | PHP 8.5 images |

### 5.2 Bootstrap & App Module

| File | Action | Changes |
|------|--------|---------|
| `src/main.ts` | Modify | `bootstrapApplication(AppComponent, appConfig)` |
| `src/app/app.module.ts` | **Delete** | Replace with `app.config.ts` |
| `src/app/app.config.ts` | **Create** | `provideHttpClient`, `provideAnimations`, `provideRouter`, interceptors |
| `src/app/app.component.ts` | Modify | `standalone: true`, `imports` array |
| `src/app/app.component.html` | Modify | Control flow syntax (@if, @for) |
| `src/polyfills.ts` | Modify | Remove core-js, keep zone.js + localize |

### 5.3 All Components — Make Standalone

Each component needs `standalone: true` and `imports` array:

| Component | Required Imports |
|-----------|-----------------|
| `AppComponent` | RouterOutlet, NgbModule, FontAwesomeModule, CommonModule, FormsModule |
| `LoginModalComponent` | CommonModule, FormsModule, ReactiveFormsModule, NgbModule |
| `StatistiekenComponent` | CommonModule, NgbModule, FontAwesomeModule |
| `EmailsComponent` | CommonModule, FontAwesomeModule, RouterModule |
| `EmailDetailComponent` | CommonModule, FontAwesomeModule, RouterModule |
| `BarcieBeschikbaarheidComponent` | CommonModule, SharedModule components |
| `TelFluitBeschikbaarheidComponent` | CommonModule, SharedModule components |
| `BarcieIndelingComponent` | CommonModule, FormsModule, ReactiveFormsModule, NgbModule, FontAwesomeModule |
| `SelecteerBarcielidComponent` | CommonModule, NgbModule |
| `MijnOverzichtComponent` | CommonModule, MatDialogModule, FontAwesomeModule |
| `RuilLijstComponent` | CommonModule, MatDialogModule, FontAwesomeModule, ReactiveFormsModule |
| `WedstrijdOverzichtComponent` | CommonModule, FontAwesomeModule |
| `TeamtakencoComponent` | CommonModule, NgbModule, FontAwesomeModule |
| `SelecteerScheidsrechterComponent` | CommonModule, NgbModule, FontAwesomeModule |
| `SelecteerTellersComponent` | CommonModule, NgbModule, FontAwesomeModule |
| `SelecteerZaalwachtComponent` | CommonModule, NgbModule, FontAwesomeModule |
| `ExportascsvComponent` | CommonModule |
| `InvalTeamsComponent` | CommonModule |
| `SpelersLijstComponent` | CommonModule |
| All shared components | CommonModule + respective dependencies |

### 5.4 Services — No Major Changes

All services already use `providedIn: 'root'`. They will work with `provideHttpClient()`. However:
- `request.service.ts` uses `forkJoin` with `share()` — check RxJS 7 `share()` replay behavior
- `emails.service.ts` uses `tap` from `rxjs/operators` — fine in v7

### 5.5 Interceptors — Convert to Functional or Keep as Class

Option A: Keep as classes with `withInterceptorsFromDI()` (simpler)
Option B: Convert to functional interceptors (recommended for Angular 22)

Functional interceptor example:
```typescript
export const impersonationInterceptor: HttpInterceptorFn = (req, next) => {
  // ... logic
  return next.handle(req);
};
```

Then in `app.config.ts`:
```typescript
provideHttpClient(withInterceptors([impersonationInterceptor, ...]))
```

---

## 6. Breaking Changes Summary

| Area | Angular 15 | Angular 22 | Impact |
|------|-----------|------------|--------|
| Bootstrapping | `platformBrowserDynamic().bootstrapModule(AppModule)` | `bootstrapApplication(AppComponent, config)` | High — rewrite main.ts |
| Modules | NgModules | Standalone components | High — all 27+ components |
| HTTP | `HttpClientModule` | `provideHttpClient()` | Medium — app.config.ts |
| Routing | `RouterModule.forRoot()` | `provideRouter()` | Medium — app.config.ts |
| Animations | `BrowserAnimationsModule` | `provideAnimations()` | Low — app.config.ts |
| Templates | `*ngIf`, `*ngFor`, `*ngSwitch` | `@if`, `@for`, `@switch` | Medium — all HTML templates |
| Zone.js | Required | Optional | Low — can keep or remove |
| TSLint | Used | **Removed** | Medium — ESLint setup |
| Protractor | Used | **Removed** | High — replace with Playwright/Cypress |
| TypeScript | 4.9.5 | 6.0.0 | Low — type strictness |
| RxJS | 6.6.3 | 7.8.0 | Low — import path changes |
| chart.js | 2.9.4 | 4.5.1 | High if used — API breaking |
| Node | 16 | 24 | Medium — Docker, CI |

---

## 7. Estimated Effort

| Phase | Tasks | Estimated Effort |
|-------|-------|-----------------|
| Phase 1 | Preparation, backup, verify build | 0.5 days |
| Phase 2 | Package.json, dependencies | 1 day |
| Phase 3 | Angular config files | 1 day |
| Phase 4 | Bootstrap + standalone conversion | 2-3 days |
| Phase 5 | Template control flow migration | 1-2 days |
| Phase 6 | RxJS 7 migration | 0.5 day |
| Phase 7 | FontAwesome 5→7 audit | 0.5 day |
| Phase 8 | Chart.js 2→4 (if used) | 1 day |
| Phase 9 | ESLint setup | 1 day |
| Phase 10 | Testing (Karma + Playwright/Cypress) | 2 days |
| Phase 11 | Docker + CI/CD | 1 day |
| Phase 12 | Bug fixes from REVIEW.md | 0.5 day |
| Phase 13 | Testing, validation, bug fixing | 2-3 days |
| **Total** | | **~16-21 days** |

---

## 8. Validation Checklist

- [ ] `npm install` succeeds with no errors
- [ ] `ng lint` passes with ESLint
- [ ] `ng build --configuration production` succeeds
- [ ] `ng test` passes (in CI with ChromeHeadless)
- [ ] All existing functionality works (login, navigation, forms, modals)
- [ ] HTTP interceptors work (auth, impersonation, credentials, headers)
- [ ] Docker build succeeds with node:24-alpine
- [ ] CI pipeline passes with Node 24

---

## 9. Rollback Plan

1. Create git branch `migration/angular-22-node-24` before starting
2. Tag the current working state: `git tag pre-angular-22-migration`
3. If migration fails, revert to tag and start fresh
4. Keep PHP backend unchanged (this is frontend-only migration)

---

## 10. Immediate Next Steps

1. **Create the migration branch**: `git checkout -b migration/angular-22-node-24`
2. **Apply package.json changes** from UPDATE.md (Angular 22.2.0, etc.)
3. **Run `npm install`** and verify no dependency conflicts
4. **Attempt `ng build`** to see what breaks with just dependency updates
5. **Start converting `main.ts` and `app.module.ts`** to standalone + `bootstrapApplication`
6. **Convert components one module at a time**, starting with leaf components (shared/) and working up to AppComponent

---

## Files That Need Changes (Complete Reference)

**Configuration:**
1. `package.json`
2. `package-lock.json` (regenerated)
3. `angular.json`
4. `tsconfig.json`
5. `src/tsconfig.app.json`
6. `src/tsconfig.spec.json`
7. `tslint.json` (DELETE)
8. `src/tslint.json` (DELETE)
9. `eslint.config.js` (CREATE)
10. `karma.conf.js`
11. `protractor.conf.js` (DELETE)
12. `conf.js` (DELETE)
13. `Dockerfile-frontend`
14. `docker/docker-compose.yml`
15. `.github/workflows/dev.yml` (if exists)

**Bootstrap & App:**
16. `src/main.ts`
17. `src/app/app.module.ts` (DELETE)
18. `src/app/app.config.ts` (CREATE)
19. `src/app/app.component.ts`
20. `src/app/app.component.html`
21. `src/polyfills.ts`

**Components (all need `standalone: true` + `imports`):**
22-48. All 27+ component `.ts`, `.html`, `.scss` files (full list in section 2.2)

**Services:**
49-59. All 10 service files (mostly compatible, check RxJS imports)

**Interceptors:**
60-63. All 4 interceptor files (convert to functional or keep with `withInterceptorsFromDI()`)

**Models:**
64-72. All 9 model files (check for strict TypeScript issues)

**Testing:**
73. `src/test.ts`
74. `src/karma.conf.js`
75. `e2e/` directory (DELETE or rewrite)

**Docs:**
76. `README.md` (update Node/Angular version references)
77. `REVIEW.md` (update migration status)
