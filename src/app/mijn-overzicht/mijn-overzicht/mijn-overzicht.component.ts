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

@Component({
  selector: 'teamportal-mijn-overzicht',
  templateUrl: './mijn-overzicht.component.html',
  styleUrls: ['./mijn-overzicht.component.scss'],
})

export class MijnOverzichtComponent implements OnInit {
  loading: boolean;
  taskIcon = faUser;
  scheidsrechterIcon = faUser;
  tellersIcon = faCalendarCheck;
  openIcon = faPlusSquare;
  dagen: any[];
  bardiensten: any[];
  eigenWedstrijden: any[];
  speeltijden: any[];
  errorMessage: string;
  dagenEmpty: boolean = false;
  user: any;
  zaalwacht = faPeopleCarry;

  infoIcon = faInfoCircle;

  constructor(
    private wordPressService: WordPressService,
    private stateService: StateService
  ) {}

  ngOnInit() {
    this.loading = true;
    this.wordPressService.GetMijnOverzicht().subscribe(
      (response) => {
        console.log(response)
        this.dagen = response;
        this.unTangleDagen()
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

  // The dagen[] is zeer fucked up met deze functie wil ik dat ding uitelkaar trekken en iets logischer opslaan

  unTangleDagen() {
    if (!this.dagen) {
      return;
    }

    this.bardiensten = this.getBarDiensten()
    // console.log(this.bardiensten)

    this.speeltijden = this.getSpeeltijden()
    console.log(this.speeltijden)
  }

  getBarDiensten() {
    return this.dagen
    .filter(dag => dag.bardiensten.length > 0)  // Filter out objects with empty bardiensten arrays
    .map(dag => dag.bardiensten);
  }

  getSpeeltijden() {
    return this.dagen
    .filter(dag => dag.speeltijden.length > 0)  // Filter out objects with empty bardiensten arrays
    .map(dag => dag.speeltijden[0]);
  }

}
