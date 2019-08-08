import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { SharedModule } from '../shared/shared.module';
import { ScheidscoComponent } from './scheidsco/scheidsco.component';
import { SelecteerScheidsrechterComponent } from './selecteer-scheidsrechter/selecteer-scheidsrechter.component';
import { SelecteerTellersComponent } from './selecteer-tellers/selecteer-tellers.component';
import { SelecteerZaalwachtComponent } from './selecteer-zaalwacht/selecteer-zaalwacht.component';

@NgModule({
  declarations: [
    ScheidscoComponent,
    SelecteerScheidsrechterComponent,
    SelecteerTellersComponent,
    SelecteerZaalwachtComponent
  ],
  imports: [CommonModule, SharedModule]
})
export class ScheidscoModule {}
  