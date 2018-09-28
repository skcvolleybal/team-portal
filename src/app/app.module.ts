import { NgModule } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { BrowserModule } from '@angular/platform-browser';
import { RouterModule, Routes } from '@angular/router';
import { FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { AppComponent } from './app.component';
import { BeschikbaarheidComponent } from './beschikbaarheid/beschikbaarheid.component';
import { CoachWedstrijdenComponent } from './coach-wedstrijden/coach-wedstrijden.component';
import { FluitAanwezigheidComponent } from './fluit-aanwezigheid/fluit-aanwezigheid.component';

import { MeespeelTeamsComponent } from './meespeel-teams/meespeel-teams.component';
import { MijnOverzichtComponent } from './mijn-overzicht/mijn-overzicht.component';
import { ScheidscoComponent } from './scheidsco/scheidsco.component';
import { ScheidsrechterComponent } from './scheidsrechter/scheidsrechter.component';
import { SpelersLijstComponent } from './spelers-lijst/spelers-lijst.component';
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
  {
    path: 'coach-aanwezigheid',
    component: CoachWedstrijdenComponent,
    data: { title: 'Coach Aanwezigheid' }
  },
  {
    path: 'fluit-aanwezigheid',
    component: FluitAanwezigheidComponent,
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
    BeschikbaarheidComponent,
    FluitAanwezigheidComponent,
    WedstrijdOverzichtComponent,
    WedstrijdenCardComponent,
    SpelersLijstComponent,
    MeespeelTeamsComponent
  ],
  imports: [
    FormsModule,
    FontAwesomeModule,
    RouterModule.forRoot(appRoutes, {
      useHash: true
    }),
    NgbModule,
    BrowserModule
  ],
  exports: [],
  providers: [{ provide: 'appRoutes', useValue: appRoutes }],
  bootstrap: [AppComponent]
})
export class AppModule {}
