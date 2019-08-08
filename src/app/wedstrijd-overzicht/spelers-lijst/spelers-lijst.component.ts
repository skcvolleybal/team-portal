import { Component, EventEmitter, Input, Output } from '@angular/core';
import {
  faMinusSquare,
  faPlusSquare,
  faTimesCircle
} from '@fortawesome/free-solid-svg-icons';

@Component({
  selector: 'teamportal-spelers-lijst',
  templateUrl: './spelers-lijst.component.html',
  styleUrls: ['./spelers-lijst.component.scss']
})
export class SpelersLijstComponent {
  @Input()
  spelers;
  @Input()
  title;
  @Input()
  class;
  @Input()
  coaches;

  @Output()
  deleteAanwezigheid = new EventEmitter();

  inklappen = faMinusSquare;
  uitklappen = faPlusSquare;
  verwijderIcon = faTimesCircle;
  isCollapsed = true;

  constructor() {}

  DeleteAanwezigheid(speler) {
    this.deleteAanwezigheid.emit(speler);
  }
}
