import { Component, OnInit } from '@angular/core';
import {
  faCalendarCheck,
  faPlusSquare,
  faUser,
} from '@fortawesome/free-solid-svg-icons';

import { JoomlaService } from '../../core/services/request.service';
import { StateService } from 'src/app/core/services/state.service';
import { ClassGetter } from '@angular/compiler/src/output/output_ast';

@Component({
  selector: 'teamportal-mijn-overzicht',
  templateUrl: './mijn-overzicht.component.html',
  styleUrls: ['./mijn-overzicht.component.scss'],
})

export class MijnOverzichtComponent implements OnInit {
  loading: boolean;
  scheidsrechterIcon = faUser;
  tellersIcon = faCalendarCheck;
  openIcon = faPlusSquare;
  dagen: any[];
  errorMessage: string;
  dagenEmpty: boolean = false;
  user: any;

  constructor(
    private joomalService: JoomlaService,
    private stateService: StateService
  ) {}

  ngOnInit() {
    this.loading = true;
    this.joomalService.GetMijnOverzicht().subscribe(
      (response) => {
        console.log(response)
        this.dagen = response;
        this.loading = false;
        if (this.dagen.length == 0) {
          this.dagenEmpty = true;
        }
      },
      (error) => {
        console.log(error);
        if (error.status === 500) {
          this.errorMessage = error.error.message;
          this.loading = false;
        }
      }
    );

    this.stateService.isAuthenticated.subscribe((isAuthenticated) => {
      if (isAuthenticated) {
        this.ngOnInit();
      }
    });

    this.joomalService.GetCurrentUser().subscribe((data) => {
      this.user = data;
    });
  }
}
