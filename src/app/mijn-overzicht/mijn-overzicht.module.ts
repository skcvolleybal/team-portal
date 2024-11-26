import { CommonModule } from '@angular/common';
import { MijnOverzichtComponent } from './mijn-overzicht/mijn-overzicht.component';
import { NgModule } from '@angular/core';
import { SharedModule } from '../shared/shared.module';
import { FontAwesomeModule } from '@fortawesome/angular-fontawesome';

@NgModule({
  declarations: [MijnOverzichtComponent],
  imports: [CommonModule, SharedModule, FontAwesomeModule],
})
export class MijnOverzichtModule {}
