import { Routes } from '@angular/router';
import { CoachWedstrijdenComponent } from './aanwezigheid/coach-wedstrijden/coach-wedstrijden.component';
import { BarcieIndelingComponent } from './barco/barcie-indeling/barcie-indeling.component';
import { BarcieBeschikbaarheidComponent } from './beschikbaarheid/barcie-beschikbaarheid/barcie-beschikbaarheid.component';
import { FluitBeschikbaarheidComponent } from './beschikbaarheid/fluit-beschikbaarheid/fluit-beschikbaarheid.component';
import { MijnOverzichtComponent } from './mijn-overzicht/mijn-overzicht/mijn-overzicht.component';
import { ScheidscoComponent } from './scheidsco/scheidsco/scheidsco.component';
import { StatistiekenComponent } from './statistiek/statistieken/statistieken.component';
import { WedstrijdOverzichtComponent } from './wedstrijd-overzicht/wedstrijd-overzicht/wedstrijd-overzicht.component';
import { WedstrijdenComponent } from './wedstrijd-overzicht/wedstrijden/wedstrijden.component';

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
    data: { title: 'Coach Aanwezigheid', groups: ['coach'] }
  },
  {
    path: 'fluit-beschikbaarheid',
    component: FluitBeschikbaarheidComponent,
    data: {
      title: 'Fluit Beschikbaarheid',
      groups: ['scheidsrechter']
    }
  },
  {
    path: 'barcie-beschikbaarheid',
    component: BarcieBeschikbaarheidComponent,
    data: {
      title: 'Barcie Beschikbaarheid',
      groups: ['barcie', 'webcie']
    }
  },
  {
    path: 'scheidsco',
    component: ScheidscoComponent,
    data: { title: 'Scheidsco', groups: ['Scheidsco', 'webcie'] }
  },
  {
    path: 'Barco',
    component: BarcieIndelingComponent,
    data: { title: 'Barco', groups: ['Scheidsco', 'webcie'] }
  },
  {
    path: 'statistieken',
    component: StatistiekenComponent,
    data: { title: 'Statistieken' }
  }
];
