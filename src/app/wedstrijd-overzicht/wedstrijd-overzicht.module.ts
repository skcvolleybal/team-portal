import { CommonModule } from '@angular/common';
import { InvalTeamsComponent } from './inval-teams/inval-teams.component';
import { NgModule } from '@angular/core';
import { SharedModule } from '../shared/shared.module';
import { SpelersLijstComponent } from './spelers-lijst/spelers-lijst.component';
import { WedstrijdOverzichtComponent } from './wedstrijd-overzicht/wedstrijd-overzicht.component';

@NgModule({
  declarations: [
    InvalTeamsComponent,
    SpelersLijstComponent,
    WedstrijdOverzichtComponent,
  ],
  imports: [CommonModule, SharedModule],
})
export class WedstrijdOverzichtModule {}
