import { Component, OnInit } from '@angular/core';
import {
  faCalendarCheck,
  faPlusSquare,
  faUser,
} from '@fortawesome/free-solid-svg-icons';

import { WordPressService } from '../../core/services/request.service';
import { StateService } from 'src/app/core/services/state.service';
import { calenderGenerator } from './calenderGenerator';

@Component({
  selector: 'teamportal-mijn-overzicht',
  templateUrl: './mijn-overzicht.component.html',
  styleUrls: ['./mijn-overzicht.component.scss'],
  providers: [calenderGenerator]
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
    private joomalService: WordPressService,
    private stateService: StateService,
    private CalendarService: calenderGenerator
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

  generateCalender() {
    this.CalendarService.generateICalendar();
  }
}
