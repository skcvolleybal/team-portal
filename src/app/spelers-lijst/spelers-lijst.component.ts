import { Component, Input, OnInit } from '@angular/core';
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
export class SpelersLijstComponent implements OnInit {
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

  ngOnInit() {
    console.log(this.spelers);
  }
}
