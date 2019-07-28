import { Component, OnInit } from '@angular/core';
import { AanwezigheidService } from '../services/aanwezigheid.service';
import { RequestService } from '../services/request.service';

@Component({
  selector: 'app-wedstrijd-overzicht',
  templateUrl: './wedstrijd-overzicht.component.html',
  styleUrls: ['./wedstrijd-overzicht.component.scss']
})
export class WedstrijdOverzichtComponent implements OnInit {
  wedstrijden: any[];
  loading: boolean;
  errorMessage: string;

  constructor(
    private aanwezigheidService: AanwezigheidService,
    private requestService: RequestService
  ) {}

  UpdateAanwezigheid(matchId, speler, aanwezigheid) {
    this.aanwezigheidService.UpdateAanwezigheid(
      matchId,
      speler.id,
      aanwezigheid
    );

    this.wedstrijden.forEach(wedstrijd => {
      if (wedstrijd.id === matchId) {
        wedstrijd.aanwezigen = wedstrijd.aanwezigen.filter(
          aanwezige => aanwezige.id !== speler.id
        );
        if (aanwezigheid === 'Ja') {
          const newSpeler = {
            id: speler.id,
            naam: speler.naam,
            isInvaller: true
          };
          wedstrijd.aanwezigen.push(newSpeler);
        }

        wedstrijd.aanwezigen = wedstrijd.aanwezigen.sort((speler1, speler2) =>
          speler1.naam > speler2.naam ? 1 : -1
        );

        return;
      }
    });
  }

  ngOnInit() {
    this.loading = true;
    this.requestService.GetWedstrijdOverzicht().subscribe(
      wedstrijden => {
        this.wedstrijden = wedstrijden;
        this.loading = false;
      },
      error => {
        if (error.status === 500) {
          this.errorMessage = error.error;
          this.loading = false;
        }
      }
    );
  }
}
