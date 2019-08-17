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

  @Output()
  deleteSpeler = new EventEmitter();

  inklappen = faMinusSquare;
  uitklappen = faPlusSquare;
  verwijderIcon = faTimesCircle;
  isCollapsed = true;

  constructor() {}

  DeleteSpeler(speler) {
    this.deleteSpeler.emit(speler);
  }
}
