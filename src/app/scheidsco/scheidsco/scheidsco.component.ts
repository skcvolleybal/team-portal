import { Component, OnInit } from '@angular/core';
import {
  faCalendarCheck,
  faPeopleCarry,
  faTimes,
  faUser
} from '@fortawesome/free-solid-svg-icons';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ScheidscoService } from '../../core/services/scheidsco.service';
import { SelecteerScheidsrechterComponent } from '../selecteer-scheidsrechter/selecteer-scheidsrechter.component';
import { SelecteerTellersComponent } from '../selecteer-tellers/selecteer-tellers.component';
import { SelecteerZaalwachtComponent } from '../selecteer-zaalwacht/selecteer-zaalwacht.component';

@Component({
  selector: 'teamportal-scheidsco',
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
    private scheidscoService: ScheidscoService,
    private modalService: NgbModal
  ) {}

  getScheidscoOverzicht() {
    this.overzichtLoading = true;
    this.scheidscoService.GetScheidscoOverzicht().subscribe(
      speeldagen => {
        this.speeldagen = speeldagen;
        this.overzichtLoading = false;
      },
      error => {
        if (error.status === 500) {
          this.errorMessage = error.error.message;
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
        this.SetZaalwacht(date, team);
      })
      .catch(() => {});
  }

  SetZaalwacht(date, team) {
    this.speeldagen.forEach(speeldag => {
      if (speeldag.date === date) {
        speeldag.zaalwacht = team;
        speeldag.zaalwachtShortNotation =
          team == null
            ? null
            : (speeldag.zaalwacht = `${team[0]}${team.substring(6)}`);
        return;
      }
    });
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
        this.SetUitvoerderOnTaak(
          geselecteerdeWedstrijd.matchId,
          taak,
          uitvoerder
        );
      })
      .catch(() => {});
  }

  SetUitvoerderOnTaak(matchId, taak, uitvoerder) {
    this.speeldagen.forEach(speeldag => {
      speeldag.speeltijden.forEach(speeltijd => {
        speeltijd.wedstrijden.forEach(wedstrijd => {
          if (wedstrijd.matchId === matchId) {
            wedstrijd[taak] = uitvoerder;
            return;
          }
        });
      });
    });
  }

  DeleteTaak(matchId: string, taak: string) {
    switch (taak) {
      case 'tellers':
        this.scheidscoService.UpdateTellers(matchId, null).subscribe();
        break;
      case 'scheidsrechter':
        this.scheidscoService.UpdateScheidsrechter(matchId, null).subscribe();
        break;
    }
    this.SetUitvoerderOnTaak(matchId, taak, null);
  }

  DeleteZaalwacht(date: string) {
    this.scheidscoService.UpdateZaalwacht(date, '').subscribe();
    this.SetZaalwacht(date, null);
  }
}
