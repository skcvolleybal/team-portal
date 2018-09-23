import { Component, Input, OnInit } from '@angular/core';
import { faCalendarCheck } from '@fortawesome/free-solid-svg-icons';

@Component({
  selector: 'app-tellers',
  templateUrl: './tellers.component.html',
  styleUrls: ['./tellers.component.scss']
})
export class TellersComponent implements OnInit {
  @Input()
  isTellers;
  @Input()
  tellers;

  teamIcon = faCalendarCheck;

  constructor() {}

  ngOnInit() {
    this.tellers = this.tellers.replace('SKC ', '');
  }
}
