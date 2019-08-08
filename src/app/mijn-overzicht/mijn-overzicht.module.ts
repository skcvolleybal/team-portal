import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { SharedModule } from '../shared/shared.module';
import { MijnOverzichtComponent } from './mijn-overzicht/mijn-overzicht.component';

@NgModule({
  declarations: [MijnOverzichtComponent],
  imports: [CommonModule, SharedModule]
})
export class MijnOverzichtModule {}
