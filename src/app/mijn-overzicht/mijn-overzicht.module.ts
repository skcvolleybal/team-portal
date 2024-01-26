import { CommonModule } from '@angular/common';
import { MijnOverzichtComponent } from './mijn-overzicht/mijn-overzicht.component';
import { NgModule } from '@angular/core';
import { SharedModule } from '../shared/shared.module';


@NgModule({
  declarations: [MijnOverzichtComponent],
  imports: [CommonModule, SharedModule],
})
export class MijnOverzichtModule {}
