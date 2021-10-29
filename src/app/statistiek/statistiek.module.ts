import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { SharedModule } from '../shared/shared.module';
import { StatistiekenComponent } from './statistieken/statistieken.component';

@NgModule({
  declarations: [StatistiekenComponent],
  imports: [CommonModule, SharedModule],
})
export class StatistiekModule {}
