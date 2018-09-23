import { Component, OnInit, Input } from '@angular/core';

@Component({
  selector: 'app-wedstrijd',
  templateUrl: './wedstrijd.component.html',
  styleUrls: ['./wedstrijd.component.scss']
})
export class WedstrijdComponent implements OnInit {
  @Input()
  wedstrijd;
  constructor() {}

  ngOnInit() {}
}
