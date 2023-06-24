import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { TeamtakencoComponent } from './teamtakenco/teamtakenco.component';
import { SelecteerScheidsrechterComponent } from './selecteer-scheidsrechter/selecteer-scheidsrechter.component';
import { SelecteerTellersComponent } from './selecteer-tellers/selecteer-tellers.component';
import { SelecteerZaalwachtComponent } from './selecteer-zaalwacht/selecteer-zaalwacht.component';
import { SharedModule } from '../shared/shared.module';

@NgModule({
    declarations: [
        TeamtakencoComponent,
        SelecteerScheidsrechterComponent,
        SelecteerTellersComponent,
        SelecteerZaalwachtComponent,
    ],
    imports: [CommonModule, SharedModule]
})
export class TeamtakencoModule {}
