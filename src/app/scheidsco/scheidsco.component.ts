import { HttpClient } from '@angular/common/http';
import { Component, OnInit } from '@angular/core';
import {
  faCalendarCheck,
  faPeopleCarry,
  faUser
} from '@fortawesome/free-solid-svg-icons';

import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { environment } from '../../environments/environment';
import { SelecteerScheidsrechterComponent } from '../selecteer-scheidsrechter/selecteer-scheidsrechter.component';
import { SelecteerTellersComponent } from '../selecteer-tellers/selecteer-tellers.component';
import { SelecteerZaalwachtComponent } from '../selecteer-zaalwacht/selecteer-zaalwacht.component';

@Component({
  selector: 'app-scheidsco',
  templateUrl: './scheidsco.component.html',
  styleUrls: ['./scheidsco.component.scss']
})
export class ScheidscoComponent implements OnInit {
  scheidsrechterIcon = faUser;
  teamIcon = faCalendarCheck;
  zaalwacht = faPeopleCarry;
  scheidsrechterType = 'niveau';

  scheidsrechters: any[];
  scheidsrechtersGroepen: any[];

  speeldagen: any[];
  teams: any[];

  scheidsrechterLoading: boolean;
  overzichtLoading: boolean;
  teamsLoading: boolean;

  errorMessage: any;

  constructor(private httpClient: HttpClient, private modalService: NgbModal) {}

  getScheidscoOverzicht() {
    this.overzichtLoading = true;

    this.httpClient
      .get<any[]>(environment.baseUrl, {
        params: {
          action: 'GetScheidscoOverzicht'
        }
      })
      .subscribe(
        speeldagen => {
          this.speeldagen = speeldagen;
          this.overzichtLoading = false;
        },
        error => {
          if (error.status === 500) {
            this.errorMessage = error.error;
            this.overzichtLoading = false;
          }
        }
      );
  }

  ngOnInit() {
    this.getScheidscoOverzicht();
  }

  SelecteerZaalwacht(datum, date) {
    const component = SelecteerZaalwachtComponent;
    component.date = date;
    component.datum = datum;
    this.modalService
      .open(component)
      .result.then(team => {
        this.speeldagen.forEach(speeldag => {
          if (speeldag.date === date) {
            speeldag.zaalwacht =
              team == null
                ? null
                : (speeldag.zaalwacht = `${team[0]}${team.substring(6)}`);
            return;
          }
        });
      })
      .catch(() => {});
  }

  SelecteerTellers(geselecteerdeWedstrijd, tijd) {
    const component = SelecteerTellersComponent;
    component.wedstrijd = geselecteerdeWedstrijd;
    component.tijd = tijd;
    this.modalService
      .open(component)
      .result.then(tellers => {
        this.speeldagen.forEach(speeldag => {
          speeldag.speeltijden.forEach(speeltijd => {
            speeltijd.wedstrijden.forEach(wedstrijd => {
              if (wedstrijd.id === geselecteerdeWedstrijd.id) {
                wedstrijd.tellers = tellers;
              }
            });
          });
        });
      })
      .catch(() => {});
  }

  SelecteerScheidsrechter(geselecteerdeWedstrijd, tijd) {
    const component = SelecteerScheidsrechterComponent;
    component.wedstrijd = geselecteerdeWedstrijd;
    component.tijd = tijd;
    this.modalService
      .open(component)
      .result.then(scheidsrechter => {
        this.speeldagen.forEach(speeldag => {
          speeldag.speeltijden.forEach(speeltijd => {
            speeltijd.wedstrijden.forEach(wedstrijd => {
              if (wedstrijd.id === geselecteerdeWedstrijd.id) {
                wedstrijd.scheidsrechter = scheidsrechter;
                return;
              }
            });
          });
        });
      })
      .catch(() => {});
  }
}
