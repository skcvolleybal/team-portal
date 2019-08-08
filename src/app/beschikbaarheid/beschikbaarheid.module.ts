import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { SharedModule } from '../shared/shared.module';
import { BarcieBeschikbaarheidComponent } from './barcie-beschikbaarheid/barcie-beschikbaarheid.component';
import { FluitBeschikbaarheidComponent } from './fluit-beschikbaarheid/fluit-beschikbaarheid.component';

@NgModule({
  declarations: [BarcieBeschikbaarheidComponent, FluitBeschikbaarheidComponent],
  imports: [CommonModule, SharedModule]
})
export class BeschikbaarheidModule {}
