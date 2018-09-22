import { BrowserModule } from '@angular/platform-browser';

import { NgModule } from '@angular/core';
import { NgbModule, NgbRadioGroup } from '@ng-bootstrap/ng-bootstrap';
import { RouterModule, Routes } from '@angular/router';

import { AppComponent } from './app.component';
import { MijnOverzichtComponent } from './mijn-overzicht/mijn-overzicht.component';

import { ScheidscoComponent } from './scheidsco/scheidsco.component';
import { StatistiekenComponent } from './statistieken/statistieken.component';
import { FormsModule } from '@angular/forms';
import { FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import { WedstrijdenComponent } from './wedstrijden/wedstrijden.component';
import { CoachWedstrijdenComponent } from './coach-wedstrijden/coach-wedstrijden.component';

const appRoutes: Routes = [
  { path: '', redirectTo: 'mijn-overzicht', pathMatch: 'full' },
  { path: 'mijn-overzicht', component: MijnOverzichtComponent, data: { 'title': 'Mijn Overzicht' } },
  { path: 'wedstrijden', component: WedstrijdenComponent, data: { 'title': 'Wedstrijden' } },
  { path: 'scheidsco', component: ScheidscoComponent, data: { 'title': 'Scheidsco' } },
  { path: 'statistieken', component: StatistiekenComponent, data: { 'title': 'Statistieken' } },
  { path: 'coach-wedstrijden', component: CoachWedstrijdenComponent, data: { 'title': 'Coach Wedstrijden' } }
];

@NgModule({
  declarations: [
    AppComponent,
    ScheidscoComponent,
    MijnOverzichtComponent,
    WedstrijdenComponent,
    StatistiekenComponent,
    CoachWedstrijdenComponent
  ],
  imports: [
    FormsModule,
    FontAwesomeModule,
    RouterModule.forRoot(appRoutes),
    NgbModule,
    BrowserModule
  ],
  exports: [

  ],
  providers: [],
  bootstrap: [AppComponent]
})
export class AppModule { }
