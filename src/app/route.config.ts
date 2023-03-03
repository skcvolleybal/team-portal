import { BarcieBeschikbaarheidComponent } from './beschikbaarheid/barcie-beschikbaarheid/barcie-beschikbaarheid.component';
import { BarcieIndelingComponent } from './barco/barcie-indeling/barcie-indeling.component';
import { MijnOverzichtComponent } from './mijn-overzicht/mijn-overzicht/mijn-overzicht.component';
import { Route } from '@angular/router';
import { ScheidscoComponent } from './scheidsco/scheidsco/scheidsco.component';
import { StatistiekenComponent } from './statistiek/statistieken/statistieken.component';
import { TelFluitBeschikbaarheidComponent } from './beschikbaarheid/tel-fluit-beschikbaarheid/tel-fluit-beschikbaarheid.component';
import { WedstrijdOverzichtComponent } from './wedstrijd-overzicht/wedstrijd-overzicht/wedstrijd-overzicht.component';

interface IToggleRoute extends Route {
  isHidden: boolean;
}

export const appRoutes: IToggleRoute[] = [
  {
    path: '',
    redirectTo: 'mijn-overzicht',
    pathMatch: 'full',
    isHidden: false,
  },
  {
    path: 'mijn-overzicht',
    component: MijnOverzichtComponent,
    data: { title: '🏠 Mijn Overzicht' },
    isHidden: false,
  },
  {
    path: 'wedstrijd-overzicht',
    component: WedstrijdOverzichtComponent,
    data: { title: '👥 Wedstrijd Beschikbaarheid' },
    isHidden: false,
  },
  // Obsolete; due to DWF 2.0 update stats need to be rewritten
  // {
  //   path: 'statistieken',
  //   component: StatistiekenComponent,
  //   data: {
  //     title: 'Statistieken',
  //   },
  //   isHidden: false,
  // },
  {
    path: 'fluit-beschikbaarheid',
    component: TelFluitBeschikbaarheidComponent,
    data: {
      title: '📆 Tel/Fluit Beschikbaarheid',
    },
    isHidden: false,
  },
  // Obsolete; is now barco
  // {
  //   path: 'barcie-beschikbaarheid',
  //   component: BarcieBeschikbaarheidComponent,
  //   data: {
  //     title: 'Barcie Beschikbaarheid',
  //     groups: ['barcie', 'webcie'],
  //   },
  //   isHidden: true,
  // },
  {
    path: 'scheidsco',
    component: ScheidscoComponent,
    data: { title: '🏁 Scheidsco', groups: ['teamcoordinator', 'webcie'] },
    isHidden: true,
  },
  {
    path: 'Barco',
    component: BarcieIndelingComponent,
    data: { title: '🍺 Barco', groups: ['teamcoordinator', 'webcie'] },
    isHidden: true,
  },
];
