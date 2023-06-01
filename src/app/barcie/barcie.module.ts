import { BarcieIndelingComponent } from './barcie-indeling/barcie-indeling.component';
import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { SelecteerBarcielidComponent } from './selecteer-barcie-lid/selecteer-barcie-lid.component';
import { SharedModule } from '../shared/shared.module';

@NgModule({
    declarations: [BarcieIndelingComponent, SelecteerBarcielidComponent],
    imports: [CommonModule, SharedModule]
})
export class BarcieModule {}
