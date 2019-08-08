import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { AanwezigheidComponent } from './aanwezigheid/aanwezigheid.component';
import { ScheidsrechterComponent } from './scheidsrechter/scheidsrechter.component';
import { SpinnerComponent } from './spinner/spinner.component';
import { TellersComponent } from './tellers/tellers.component';
import { WedstrijdComponent } from './wedstrijd/wedstrijd.component';
import { WedstrijdenCardComponent } from './wedstrijden-card/wedstrijden-card.component';

@NgModule({
  imports: [CommonModule, FontAwesomeModule, NgbModule, FormsModule],
  declarations: [
    WedstrijdComponent,
    ScheidsrechterComponent,
    TellersComponent,
    SpinnerComponent,
    WedstrijdenCardComponent,
    AanwezigheidComponent
  ],
  exports: [
    FontAwesomeModule,
    FormsModule,
    ReactiveFormsModule,
    NgbModule,
    WedstrijdComponent,
    ScheidsrechterComponent,
    AanwezigheidComponent,
    TellersComponent,
    SpinnerComponent,
    WedstrijdenCardComponent
  ]
})
export class SharedModule {}
