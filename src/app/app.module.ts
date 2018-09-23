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
import { MijnOverzichtComponent } from './mijn-overzicht/mijn-overzicht.component';
import { ScheidscoComponent } from './scheidsco/scheidsco.component';
import { ScheidsrechterComponent } from './scheidsrechter/scheidsrechter.component';
import { StatistiekenComponent } from './statistieken/statistieken.component';
import { TellersComponent } from './tellers/tellers.component';
import { WedstrijdOverzichtComponent } from './wedstrijd-overzicht/wedstrijd-overzicht.component';
import { WedstrijdComponent } from './wedstrijd/wedstrijd.component';
import { WedstrijdenComponent } from './wedstrijden/wedstrijden.component';

const appRoutes: Routes = [
  { path: '', redirectTo: 'mijn-overzicht', pathMatch: 'full' },
  {
    path: 'mijn-overzicht',
    component: MijnOverzichtComponent,
    data: { title: 'Mijn Overzicht' }
  },
  {
    path: 'wedstrijden',
    component: WedstrijdenComponent,
    data: { title: 'Wedstrijden' }
  },
  {
    path: 'wedstrijd-overzicht',
    component: WedstrijdOverzichtComponent,
    data: { title: 'Wedstrijd Overzicht' }
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
  },
  {
    path: 'coach-wedstrijden',
    component: CoachWedstrijdenComponent,
    data: { title: 'Coach Wedstrijden' }
  },
  {
    path: 'fluit-aanwezigheid',
    component: FluitAanwezigheidComponent,
    data: { title: 'Fluit Aanwezigheid' }
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
    WedstrijdOverzichtComponent
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
  providers: [],
  bootstrap: [AppComponent]
})
export class AppModule {}
