import { HTTP_INTERCEPTORS, HttpClientModule } from '@angular/common/http';
import { NgModule } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { BrowserModule } from '@angular/platform-browser';
import { RouterModule, Routes } from '@angular/router';
import { FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { AanwezigheidComponent } from './aanwezigheid/aanwezigheid.component';
import { AppComponent } from './app.component';
import { CoachWedstrijdenComponent } from './coach-wedstrijden/coach-wedstrijden.component';
import { FluitBeschikbaarheidComponent } from './fluit-beschikbaarheid/fluit-beschikbaarheid.component';
import { CustomInterceptor } from './interceptors/add-credentials.interceptor';
import { HTTPListener } from './interceptors/is-authorized.interceptor';
import { InvalTeamsComponent } from './inval-teams/inval-teams.component';
import { LoginModalComponent } from './login-modal/login-modal.component';
import { MijnOverzichtComponent } from './mijn-overzicht/mijn-overzicht.component';
import { ScheidscoComponent } from './scheidsco/scheidsco.component';
import { ScheidsrechterComponent } from './scheidsrechter/scheidsrechter.component';
import { AuthenticationService } from './services/authentication.service';
import { SpelersLijstComponent } from './spelers-lijst/spelers-lijst.component';
import { SpinnerComponent } from './spinner/spinner.component';
import { StatistiekenComponent } from './statistieken/statistieken.component';
import { TellersComponent } from './tellers/tellers.component';
import { WedstrijdOverzichtComponent } from './wedstrijd-overzicht/wedstrijd-overzicht.component';
import { WedstrijdComponent } from './wedstrijd/wedstrijd.component';
import { WedstrijdenCardComponent } from './wedstrijden-card/wedstrijden-card.component';
import { WedstrijdenComponent } from './wedstrijden/wedstrijden.component';

export const appRoutes: Routes = [
  { path: '', redirectTo: 'mijn-overzicht', pathMatch: 'full' },
  {
    path: 'mijn-overzicht',
    component: MijnOverzichtComponent,
    data: { title: 'Mijn Overzicht' }
  },
  {
    path: 'wedstrijd-aanwezigheid',
    component: WedstrijdenComponent,
    data: { title: 'Wedstrijd Aanwezigheid' }
  },
  {
    path: 'wedstrijd-overzicht',
    component: WedstrijdOverzichtComponent,
    data: { title: 'Wedstrijd Overzicht' }
  },
  //   {
  //     path: 'coach-aanwezigheid',
  //     component: CoachWedstrijdenComponent,
  //     data: { title: 'Coach Aanwezigheid' }
  //   },
  {
    path: 'fluit-aanwezigheid',
    component: FluitBeschikbaarheidComponent,
    data: { title: 'Fluit Aanwezigheid' }
  },
  {
    path: 'scheidsco',
    component: ScheidscoComponent,
    data: { title: 'Scheidsco' }
  },
  {
    path: 'statistieken',
    component: StatistiekenComponent,
    data: { title: 'Statistieken' }
  }
];

@NgModule({
  entryComponents: [LoginModalComponent],
  declarations: [
    AppComponent,
    ScheidscoComponent,
    MijnOverzichtComponent,
    WedstrijdenComponent,
    StatistiekenComponent,
    CoachWedstrijdenComponent,
    TellersComponent,
    ScheidsrechterComponent,
    WedstrijdComponent,
    AanwezigheidComponent,
    FluitBeschikbaarheidComponent,
    WedstrijdOverzichtComponent,
    WedstrijdenCardComponent,
    SpelersLijstComponent,
    InvalTeamsComponent,
    LoginModalComponent,
    SpinnerComponent
  ],
  imports: [
    FormsModule,
    FontAwesomeModule,
    RouterModule.forRoot(appRoutes, {
      useHash: true
    }),
    NgbModule,
    BrowserModule,
    HttpClientModule
  ],
  exports: [],
  providers: [
    AuthenticationService,
    { provide: 'appRoutes', useValue: appRoutes },
    {
      provide: HTTP_INTERCEPTORS,
      useClass: CustomInterceptor,
      multi: true
    },
    {
      provide: HTTP_INTERCEPTORS,
      useClass: HTTPListener,
      multi: true
    }
  ],
  bootstrap: [AppComponent]
})
export class AppModule {}
