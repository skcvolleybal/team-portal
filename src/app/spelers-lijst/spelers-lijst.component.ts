import { Component, Input } from '@angular/core';
import {
  faMinusSquare,
  faPlusSquare,
  faTimesCircle
} from '@fortawesome/free-solid-svg-icons';

@Component({
  selector: 'app-spelers-lijst',
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

  inklappen = faMinusSquare;
  uitklappen = faPlusSquare;
  verwijderIcon = faTimesCircle;
  isCollapsed = true;

  constructor() {}
}
