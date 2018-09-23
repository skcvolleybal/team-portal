import { Component, OnInit, Input } from '@angular/core';
import { faUsers } from '@fortawesome/free-solid-svg-icons';

@Component({
  selector: 'app-scheidsrechter',
  templateUrl: './scheidsrechter.component.html',
  styleUrls: ['./scheidsrechter.component.scss']
})
export class ScheidsrechterComponent implements OnInit {
  @Input()
  isScheidsrechter;
  @Input()
  scheidsrechter;

  scheidsrechterIcon = faUsers;

  constructor() {}

  ngOnInit() {}
}
