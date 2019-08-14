import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { SharedModule } from '../shared/shared.module';
import { BarcieIndelingComponent } from './barcie-indeling/barcie-indeling.component';
import { SelecteerBarcielidComponent } from './selecteer-barcie-lid/selecteer-barcie-lid.component';

@NgModule({
  entryComponents: [SelecteerBarcielidComponent],
  declarations: [BarcieIndelingComponent, SelecteerBarcielidComponent],
  imports: [CommonModule, SharedModule]
})
export class BarcoModule {}
