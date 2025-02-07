import { CommonModule } from '@angular/common';
import { MijnOverzichtComponent } from './mijn-overzicht/mijn-overzicht.component';
import { NgModule } from '@angular/core';
import { SharedModule } from '../shared/shared.module';
import { RuilLijstComponent } from './ruil-lijst/ruil-lijst.component';

@NgModule({
  declarations: [MijnOverzichtComponent, RuilLijstComponent],
  imports: [CommonModule, SharedModule],
})
export class MijnOverzichtModule {}
