import { Component, Input, OnInit } from '@angular/core';
import {
  faMinusSquare,
  faPlus,
  faPlusSquare
} from '@fortawesome/free-solid-svg-icons';

@Component({
  selector: 'app-meespeel-teams',
  templateUrl: './meespeel-teams.component.html',
  styleUrls: ['./meespeel-teams.component.scss']
})
export class MeespeelTeamsComponent implements OnInit {
  @Input()
  teams;

  uitklappen = faPlusSquare;
  inklappen = faMinusSquare;
  spelerToevoegen = faPlus;
  isCollapsed = true;
  isTeamCollapsed: boolean[];
  constructor() {}

  ngOnInit() {
    this.isTeamCollapsed = new Array(this.teams.length).fill(true);
  }
}
