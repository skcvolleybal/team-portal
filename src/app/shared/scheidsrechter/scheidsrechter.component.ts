import { Component, Input, OnInit } from '@angular/core';

import { faUser } from '@fortawesome/free-solid-svg-icons';

@Component({
  selector: 'teamportal-scheidsrechter',
  templateUrl: './scheidsrechter.component.html',
  styleUrls: ['./scheidsrechter.component.scss'],
})
export class ScheidsrechterComponent implements OnInit {
  @Input()
  isScheidsrechter;
  @Input()
  scheidsrechter;

  scheidsrechterIcon = faUser;

  constructor() {}

  ngOnInit() {}
}
