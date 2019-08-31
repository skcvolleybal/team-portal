import { Route } from '@angular/router';
import { BarcieIndelingComponent } from './barco/barcie-indeling/barcie-indeling.component';
import { BarcieBeschikbaarheidComponent } from './beschikbaarheid/barcie-beschikbaarheid/barcie-beschikbaarheid.component';
import { FluitBeschikbaarheidComponent } from './beschikbaarheid/fluit-beschikbaarheid/fluit-beschikbaarheid.component';
import { MijnOverzichtComponent } from './mijn-overzicht/mijn-overzicht/mijn-overzicht.component';
import { ScheidscoComponent } from './scheidsco/scheidsco/scheidsco.component';
import { StatistiekenComponent } from './statistiek/statistieken/statistieken.component';
import { WedstrijdOverzichtComponent } from './wedstrijd-overzicht/wedstrijd-overzicht/wedstrijd-overzicht.component';

interface IToggleRoute extends Route {
  isHidden: boolean;
}

export const appRoutes: IToggleRoute[] = [
  {
    path: '',
    redirectTo: 'mijn-overzicht',
    pathMatch: 'full',
    isHidden: false
  },
  {
    path: 'mijn-overzicht',
    component: MijnOverzichtComponent,
    data: { title: 'Mijn Overzicht' },
    isHidden: false
  },
  {
    path: 'wedstrijd-overzicht',
    component: WedstrijdOverzichtComponent,
    data: { title: 'Wedstrijd Overzicht' },
    isHidden: false
  },
  {
    path: 'fluit-beschikbaarheid',
    component: FluitBeschikbaarheidComponent,
    data: {
      title: 'Fluit Beschikbaarheid',
      groups: ['scheidsrechter']
    },
    isHidden: true
  },
  {
    path: 'barcie-beschikbaarheid',
    component: BarcieBeschikbaarheidComponent,
    data: {
      title: 'Barcie Beschikbaarheid',
      groups: ['barcie', 'webcie']
    },
    isHidden: true
  },
  {
    path: 'scheidsco',
    component: ScheidscoComponent,
    data: { title: 'Scheidsco', groups: ['Scheidsco', 'webcie'] },
    isHidden: true
  },
  {
    path: 'Barco',
    component: BarcieIndelingComponent,
    data: { title: 'Barco', groups: ['Scheidsco', 'webcie'] },
    isHidden: true
  },
  {
    path: 'statistieken',
    component: StatistiekenComponent,
    data: { title: 'Statistieken' },
    isHidden: true
  }
];
