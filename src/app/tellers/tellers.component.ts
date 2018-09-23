import { Component, OnInit, Input } from '@angular/core';
import { faUsers } from '@fortawesome/free-solid-svg-icons';

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

  teamIcon = faUsers;

  constructor() {}

  ngOnInit() {}
}
