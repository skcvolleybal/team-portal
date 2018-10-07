import { Component, Input, OnInit } from '@angular/core';
import { faCalendarCheck } from '@fortawesome/free-solid-svg-icons';

@Component({
  selector: 'app-tellers',
  templateUrl: './tellers.component.html',
  styleUrls: ['./tellers.component.scss']
})
export class TellersComponent implements OnInit {
  @Input()
  isTelteam;
  @Input()
  telteam;

  teamIcon = faCalendarCheck;

  constructor() {}

  ngOnInit() {
    if (this.telteam) {
      this.telteam = this.telteam.replace('SKC ', '');
    }
  }
}
