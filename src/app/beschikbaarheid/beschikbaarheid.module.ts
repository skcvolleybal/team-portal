import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { SharedModule } from '../shared/shared.module';
import { BarcieBeschikbaarheidComponent } from './barcie-beschikbaarheid/barcie-beschikbaarheid.component';
import { TelFluitBeschikbaarheidComponent } from './tel-fluit-beschikbaarheid/tel-fluit-beschikbaarheid.component';

@NgModule({
  declarations: [BarcieBeschikbaarheidComponent, TelFluitBeschikbaarheidComponent],
  imports: [CommonModule, SharedModule]
})
export class BeschikbaarheidModule {}
