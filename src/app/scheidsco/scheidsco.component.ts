import { Component, OnInit } from '@angular/core';
import {
  faCalendarCheck,
  faPeopleCarry,
  faTimes,
  faUser
} from '@fortawesome/free-solid-svg-icons';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { SelecteerScheidsrechterComponent } from '../selecteer-scheidsrechter/selecteer-scheidsrechter.component';
import { SelecteerTellersComponent } from '../selecteer-tellers/selecteer-tellers.component';
import { SelecteerZaalwachtComponent } from '../selecteer-zaalwacht/selecteer-zaalwacht.component';
import { RequestService } from '../services/RequestService';

@Component({
  selector: 'app-scheidsco',
  templateUrl: './scheidsco.component.html',
  styleUrls: ['./scheidsco.component.scss']
})
export class ScheidscoComponent implements OnInit {
  icons = {
    scheidsrechter: faUser,
    tellers: faCalendarCheck,
    zaalwacht: faPeopleCarry,
    verwijderen: faTimes
  };

  scheidsrechters: any[];
  speeldagen: any[];
  taken = ['scheidsrechter', 'tellers'];
  overzichtLoading: boolean;
  errorMessage: any;

  constructor(
    private requestService: RequestService,
    private modalService: NgbModal
  ) {}

  getScheidscoOverzicht() {
    this.overzichtLoading = true;
    this.requestService.GetScheidscoOverzicht().subscribe(
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

  SelecteerUitvoerderVanTaak(taak, geselecteerdeWedstrijd, tijd) {
    const component =
      taak === 'scheidsrechter'
        ? SelecteerScheidsrechterComponent
        : SelecteerTellersComponent;
    component.wedstrijd = geselecteerdeWedstrijd;
    component.tijd = tijd;
    this.modalService
      .open(component)
      .result.then(uitvoerder => {
        this.speeldagen.forEach(speeldag => {
          speeldag.speeltijden.forEach(speeltijd => {
            speeltijd.wedstrijden.forEach(wedstrijd => {
              if (wedstrijd.id === geselecteerdeWedstrijd.id) {
                wedstrijd[taak] = uitvoerder;
                return;
              }
            });
          });
        });
      })
      .catch(() => {});
  }

  DeleteTaak(taak, matchId) {
    switch (taak) {
      case 'tellers':
        this.requestService.UpdateTellers(matchId, null);
        break;
      case 'scheidsrechter':
        this.requestService.UpdateScheidsrechter(matchId, null);
        break;
    }
  }

  GetClassForTaak(taak, wedstrijd) {
    return {
      'btn-danger': wedstrijd[taak] === null,
      'btn-success': wedstrijd.scheidsrechter
    };
  }
}
