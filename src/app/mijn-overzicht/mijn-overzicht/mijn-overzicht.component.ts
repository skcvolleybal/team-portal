import { Component, OnInit } from '@angular/core';
import { calenderGenerator } from '../../core/services/calenderGenerator';

import {
  faCalendarCheck,
  faPlusSquare,
  faUser,
  faInfoCircle,
  faPeopleCarry,
  faCalendar,
} from '@fortawesome/free-solid-svg-icons';

import { WordPressService } from '../../core/services/request.service';
import { StateService } from 'src/app/core/services/state.service';

@Component({
  selector: 'teamportal-mijn-overzicht',
  templateUrl: './mijn-overzicht.component.html',
  styleUrls: ['./mijn-overzicht.component.scss'],
  providers: [calenderGenerator]
})

export class MijnOverzichtComponent implements OnInit {
  loading: boolean;
  taskIcon = faUser;
  scheidsrechterIcon = faUser;
  tellersIcon = faCalendarCheck;
  calenderIcon = faCalendar;
  openIcon = faPlusSquare;
  dagen: any[];
  errorMessage: string;
  dagenEmpty: boolean = false;
  user: any;
  zaalwacht = faPeopleCarry;

  infoIcon = faInfoCircle;

  constructor(
    private wordPressService: WordPressService,
    private stateService: StateService,
    private CalendarService: calenderGenerator
  ) {}

  ngOnInit() {
    this.loading = true;
    this.wordPressService.GetMijnOverzicht().subscribe(
      (response) => {
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

    this.wordPressService.GetCurrentUser().subscribe((data) => {
      this.user = data;
    });
  }

  // For ICAL file this one
  generateCalender() {
    this.CalendarService.generateICalendar(this.user);
  }

  // async generateCalender(): Promise<void> {
  //   await this.CalendarService.initializeCalendar();
  // }

}
