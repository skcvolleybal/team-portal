import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { KeuzeComponent } from './keuze/keuze.component';
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
    KeuzeComponent
  ],
  exports: [
    FontAwesomeModule,
    FormsModule,
    ReactiveFormsModule,
    NgbModule,
    WedstrijdComponent,
    ScheidsrechterComponent,
    KeuzeComponent,
    TellersComponent,
    SpinnerComponent,
    WedstrijdenCardComponent
  ]
})
export class SharedModule {}
