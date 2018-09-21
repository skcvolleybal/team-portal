import { BrowserModule } from '@angular/platform-browser';

import { NgModule } from '@angular/core';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { RouterModule, Routes } from '@angular/router';

import { AppComponent } from './app.component';
import { MijnOverzichtComponent } from './mijn-overzicht/mijn-overzicht.component';
import { AanwezigheidComponent } from './aanwezigheid/aanwezigheid.component';
import { ScheidscoComponent } from './scheidsco/scheidsco.component';
import { StatistiekenComponent } from './statistieken/statistieken.component';
import { FormsModule } from '@angular/forms';

const appRoutes: Routes = [
  { path: '', redirectTo: 'mijn-overzicht', pathMatch: 'full' },
  { path: 'mijn-overzicht', component: MijnOverzichtComponent },
  { path: 'aanwezigheid', component: AanwezigheidComponent },
  { path: 'scheidsco', component: ScheidscoComponent },
  { path: 'statistieken', component: StatistiekenComponent }
];

@NgModule({
  declarations: [
    AppComponent,
    ScheidscoComponent,
    MijnOverzichtComponent,
    AanwezigheidComponent,
    StatistiekenComponent
  ],
  imports: [
    FormsModule,
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
