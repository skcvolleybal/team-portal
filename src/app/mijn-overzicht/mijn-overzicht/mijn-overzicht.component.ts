import { Component, OnInit } from '@angular/core';
import {
  faCalendarCheck,
  faPlusSquare,
  faUser,
  faInfoCircle,
  faPeopleCarry,
} from '@fortawesome/free-solid-svg-icons';

import { WordPressService } from '../../core/services/request.service';
import { StateService } from 'src/app/core/services/state.service';

import { DatePipe } from '@angular/common';


@Component({
  selector: 'teamportal-mijn-overzicht',
  templateUrl: './mijn-overzicht.component.html',
  styleUrls: ['./mijn-overzicht.component.scss'],
  providers: [DatePipe],
})

export class MijnOverzichtComponent implements OnInit {
  loading: boolean;
  taskIcon = faUser;
  scheidsrechterIcon = faUser;
  tellersIcon = faCalendarCheck;
  openIcon = faPlusSquare;
  dagen: any[];
  errorMessage: string;
  dagenEmpty: boolean = false;
  user: any;
  zaalwacht = faPeopleCarry;
  teamtaken: any[];

  infoIcon = faInfoCircle;

  constructor(
    private wordPressService: WordPressService,
    private stateService: StateService,
    private datePipe: DatePipe
  ) {}

  ngOnInit() {
    this.loading = true;
    this.wordPressService.GetMijnOverzicht().subscribe(
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

    this.wordPressService.GetMijnTeamtaken().subscribe(
      (response) => {
        console.log(response);
        console.log("Getting mijn teamtaken");
        this.teamtaken = response;
        this.loading = false;
        this.sortTasks();
        this.removeFutureTasks();
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

  removeFutureTasks() {
    const today = new Date();
    this.teamtaken = this.teamtaken.filter(task => new Date(task.date) <= today);  }

  sortTasks() {
    this.teamtaken.sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime());
  }

  isPast(taskDate: string): boolean {
    return new Date(taskDate) < new Date();
  }

  isFuture(taskDate: string): boolean {
    return new Date(taskDate) > new Date();
  }

  formatDate(date: string): string {
    return this.datePipe.transform(date, 'EEE, MMM d, y') || '';
  }

  isTeamtaak(wedstrijd: any): boolean {
    return wedstrijd.team1 !== this.user.team.naam && wedstrijd.team2 !== this.user.team.naam;
  }
  

}
