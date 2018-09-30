import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import {
  faCheck,
  faQuestion,
  faTimes
} from '@fortawesome/free-solid-svg-icons';

@Component({
  selector: 'app-aanwezigheid',
  templateUrl: './aanwezigheid.component.html',
  styleUrls: ['./aanwezigheid.component.scss']
})
export class AanwezigheidComponent {
  @Input()
  aanwezigheid;
  @Output()
  updateAanwezigheid = new EventEmitter();

  jaIcon = faCheck;
  misschienIcon = faQuestion;
  neeIcon = faTimes;

  onClick(aanwezigheid) {
    this.updateAanwezigheid.emit(aanwezigheid);
  }

  constructor() {}
}
