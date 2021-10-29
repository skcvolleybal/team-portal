import { Component, Input, OnInit } from '@angular/core';

@Component({
  selector: 'teamportal-wedstrijden-card',
  templateUrl: './wedstrijden-card.component.html',
  styleUrls: ['./wedstrijden-card.component.scss'],
})
export class WedstrijdenCardComponent implements OnInit {
  @Input()
  wedstrijden;

  constructor() {}

  ngOnInit() {}
}
