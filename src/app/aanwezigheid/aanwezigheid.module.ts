import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { SharedModule } from '../shared/shared.module';
import { CoachWedstrijdenComponent } from './coach-wedstrijden/coach-wedstrijden.component';

@NgModule({
  imports: [SharedModule, CommonModule],
  declarations: [CoachWedstrijdenComponent]
})
export class AanwezigheidModule {}
