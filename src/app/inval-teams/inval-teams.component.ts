import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import {
  faInfoCircle,
  faMinusSquare,
  faPlus,
  faPlusSquare
} from '@fortawesome/free-solid-svg-icons';

@Component({
  selector: 'app-inval-teams',
  templateUrl: './inval-teams.component.html',
  styleUrls: ['./inval-teams.component.scss']
})
export class InvalTeamsComponent implements OnInit {
  @Input()
  teams;
  @Output()
  addAanwezigheid = new EventEmitter();

  uitklappen = faPlusSquare;
  inklappen = faMinusSquare;
  spelerToevoegen = faPlus;
  info = faInfoCircle;

  isCollapsed = true;
  isTeamCollapsed: boolean[];
  constructor() {}

  AddAanwezigheid(speler) {
    this.addAanwezigheid.emit(speler);
  }

  ngOnInit() {
    if (this.teams) {
      this.isTeamCollapsed = new Array(this.teams.length).fill(true);
    }
  }
}
