import { BarcieBeschikbaarheidComponent } from './beschikbaarheid/barcie-beschikbaarheid/barcie-beschikbaarheid.component';
import { BarcieIndelingComponent } from './barcie/barcie-indeling/barcie-indeling.component';
import { MijnOverzichtComponent } from './mijn-overzicht/mijn-overzicht/mijn-overzicht.component';
import { Route } from '@angular/router';
import { TeamtakencoComponent } from './teamtakenco/teamtakenco/teamtakenco.component';
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
  // Obsolete; is now barcie
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
    path: 'teamtakenco',
    component: TeamtakencoComponent,
    data: { title: '🏁 TeamTakenCo', groups: ['teamcoordinator', 'webcie'] },
    isHidden: true,
  },
  {
    path: 'Barcie',
    component: BarcieIndelingComponent,
    data: { title: '🍺 Barcie', groups: ['teamcoordinator', 'webcie'] },
    isHidden: true,
  },
];
