import { Component, Input, OnInit } from '@angular/core';

@Component({
  selector: 'teamportal-wedstrijd',
  templateUrl: './wedstrijd.component.html',
  styleUrls: ['./wedstrijd.component.scss'],
})
export class WedstrijdComponent implements OnInit {
  @Input()
  wedstrijd;
  constructor() {}

  ngOnInit() {}
}
