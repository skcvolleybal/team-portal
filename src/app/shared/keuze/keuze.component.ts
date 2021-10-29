import { Component, EventEmitter, Input, Output } from '@angular/core';
import {
  faCheck,
  faQuestion,
  faTimes,
} from '@fortawesome/free-solid-svg-icons';

@Component({
  selector: 'teamportal-keuze',
  templateUrl: './keuze.component.html',
  styleUrls: ['./keuze.component.scss'],
})
export class KeuzeComponent {
  @Input()
  keuze;
  @Output()
  updateKeuze = new EventEmitter();

  jaIcon = faCheck;
  onbekendIcon = faQuestion;
  neeIcon = faTimes;

  onClick(keuze) {
    this.keuze = keuze;
    this.updateKeuze.emit(keuze);
  }
}
