import { Component, OnInit } from '@angular/core';
import {
  faCalendarCheck,
  faPeopleCarry,
  faTrashAlt,
  faUser,
} from '@fortawesome/free-solid-svg-icons';

import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ScheidscoService } from '../../core/services/scheidsco.service';
import { SelecteerScheidsrechterComponent } from '../selecteer-scheidsrechter/selecteer-scheidsrechter.component';
import { SelecteerTellersComponent } from '../selecteer-tellers/selecteer-tellers.component';
import { SelecteerZaalwachtComponent } from '../selecteer-zaalwacht/selecteer-zaalwacht.component';
import { Speeldag } from 'src/app/models/Speeldag';
import { Wedstrijd } from 'src/app/models/Wedstrijd';

@Component({
  selector: 'teamportal-scheidsco',
  templateUrl: './scheidsco.component.html',
  styleUrls: ['./scheidsco.component.scss'],
})
export class ScheidscoComponent implements OnInit {
  icons = {
    scheidsrechter: faUser,
    tellers: faCalendarCheck,
    zaalwacht: faPeopleCarry,
    verwijderen: faTrashAlt,
  };

  scheidsrechters: any[];
  speeldagen: Speeldag[];
  taken = ['scheidsrechter', 'tellers'];
  overzichtLoading: boolean;
  errorMessage: any;
  speeldagenEmpty:boolean = false;
  constructor(
    private scheidscoService: ScheidscoService,
    private modalService: NgbModal
  ) {}

  getScheidscoOverzicht() {
    this.overzichtLoading = true;
    this.scheidscoService.GetScheidscoOverzicht().subscribe(
      (speeldagen) => {
        this.speeldagen = speeldagen;
        this.overzichtLoading = false;
        if (this.speeldagen.length == 0) {
          this.speeldagenEmpty = true;
        }
      },
      (error) => {
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

  SelecteerZaalwacht(datum: string, date: string, zaalwachttype: string) {
    const component = SelecteerZaalwachtComponent;
    component.date = date;
    component.datum = datum;
    component.zaalwachttype = zaalwachttype;
    this.modalService
      .open(component)
      .result.then((team) => {
        this.SetZaalwacht(date, team, zaalwachttype);
      })
      .catch(() => {});
  }

  getShortNotation(name: string): string {
    if (!name) {
      return;
    }
    const gender = name.charAt(0);
    const teamnumber = name.substring(6);
    return `${gender}${teamnumber}`;
  }

  SetZaalwacht(date: string, team: string, zaalwachttype: string) {
    const speeldag = this.speeldagen.find((dag) => dag.date === date);
    if (zaalwachttype === 'eerste') {
      speeldag.eersteZaalwacht = team;
      speeldag.eersteZaalwachtShortNotation = this.getShortNotation(team);
    } else {
      speeldag.tweedeZaalwacht = team;
      speeldag.tweedeZaalwachtShortNotation = this.getShortNotation(team);
    }
  }

  SelecteerScheidsrechter(geselecteerdeWedstrijd: Wedstrijd, tijd: string) {
    const component = SelecteerScheidsrechterComponent;
    component.wedstrijd = geselecteerdeWedstrijd;
    component.tijd = tijd;
    const $this = this;
    this.modalService
      .open(component)
      .result.then((result) =>
        $this.SetScheidsrechter(geselecteerdeWedstrijd.matchId, result)
      )
      .catch(() => {});
  }

  SelecteerTeller(
    geselecteerdeWedstrijd: Wedstrijd,
    tijd: string,
    tellerIndex: number
  ) {
    const component = SelecteerTellersComponent;
    component.tellerIndex = tellerIndex;
    component.wedstrijd = geselecteerdeWedstrijd;
    component.tijd = tijd;
    this.modalService
      .open(component)
      .result.then((result) => {
        this.SetTeller(
          geselecteerdeWedstrijd.matchId,
          result.teller.naam,
          result.tellerIndex
        );
      })
      .catch(() => {});
  }

  SetScheidsrechter(matchId, scheidsrechter) {
    this.speeldagen.forEach((speeldag) => {
      speeldag.speeltijden.forEach((speeltijd) => {
        speeltijd.wedstrijden.forEach((wedstrijd) => {
          if (wedstrijd.matchId === matchId) {
            wedstrijd.scheidsrechter = scheidsrechter;
            return;
          }
        });
      });
    });
  }

  SetTeller(matchId, teller, tellerIndex) {
    this.speeldagen.forEach((speeldag) => {
      speeldag.speeltijden.forEach((speeltijd) => {
        speeltijd.wedstrijden.forEach((wedstrijd) => {
          if (wedstrijd.matchId === matchId) {
            wedstrijd.tellers[tellerIndex] = teller;
            return;
          }
        });
      });
    });
  }

  DeleteScheidsrechter(matchId: string) {
    this.scheidscoService.UpdateScheidsrechter(matchId, null).subscribe();
    this.SetScheidsrechter(matchId, null);
  }

  DeleteTeller(matchId: string, tellerIndex: number) {
    this.scheidscoService.UpdateTellers(matchId, null, tellerIndex).subscribe();
    this.SetTeller(matchId, null, tellerIndex);
  }

  DeleteZaalwacht(date: string, zaalwachttype: string) {
    this.scheidscoService
      .UpdateZaalwacht(date, null, zaalwachttype)
      .subscribe();
    this.SetZaalwacht(date, null, zaalwachttype);
  }
}
