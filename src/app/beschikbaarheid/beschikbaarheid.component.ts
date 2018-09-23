import { Component, Input, OnInit } from '@angular/core';
import {
  faCheck,
  faQuestion,
  faTimes
} from '@fortawesome/free-solid-svg-icons';

@Component({
  selector: 'app-beschikbaarheid',
  templateUrl: './beschikbaarheid.component.html',
  styleUrls: ['./beschikbaarheid.component.scss']
})
export class BeschikbaarheidComponent implements OnInit {
  @Input()
  beschikbaarheid;

  jaIcon = faCheck;
  misschienIcon = faQuestion;
  neeIcon = faTimes;

  constructor() {}

  ngOnInit() {}
}
