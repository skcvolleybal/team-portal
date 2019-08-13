import { Component, OnInit } from '@angular/core';
import { AanwezigheidService } from '../../core/services/aanwezigheid.service';
import { RequestService } from '../../core/services/request.service';

@Component({
  selector: 'teamportal-wedstrijd-overzicht',
  templateUrl: './wedstrijd-overzicht.component.html',
  styleUrls: ['./wedstrijd-overzicht.component.scss']
})
export class WedstrijdOverzichtComponent implements OnInit {
  wedstrijden: any[];
  loading: boolean;
  errorMessage: string;
  user: any;

  constructor(
    private aanwezigheidService: AanwezigheidService,
    private requestService: RequestService
  ) {}

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

    this.requestService.GetCurrentUser().subscribe(data => {
      this.user = data;
    });
  }

  UpdateAanwezigheid(matchId: number, aanwezigheid: string, speler: any) {
    this.aanwezigheidService.UpdateAanwezigheid(
      matchId,
      aanwezigheid,
      speler.id
    );

    this.wedstrijden.forEach(wedstrijd => {
      if (wedstrijd.id === matchId) {
        wedstrijd.afwezigen = wedstrijd.afwezigen.filter(
          afwezige => afwezige.id !== speler.id
        );
        wedstrijd.aanwezigen = wedstrijd.aanwezigen.filter(
          aanwezige => aanwezige.id !== speler.id
        );
        wedstrijd.onbekend = wedstrijd.onbekend.filter(
          onbekende => onbekende.id !== speler.id
        );
        const newSpeler = {
          id: speler.id,
          naam: speler.naam,
          isInvaller: speler.id !== this.user.id
        };

        const SortTeam = (speler1, speler2) =>
          speler1.naam > speler2.naam ? 1 : -1;

        switch (aanwezigheid) {
          case 'Ja':
            wedstrijd.aanwezigen.push(newSpeler);
            wedstrijd.aanwezigen.sort(SortTeam);
            break;
          case 'Nee':
            wedstrijd.afwezigen.push(newSpeler);
            wedstrijd.afwezigen.sort(SortTeam);
            break;
          case 'Onbekend':
            wedstrijd.onbekend.push(newSpeler);
            wedstrijd.onbekend.sort(SortTeam);
            break;
        }

        return;
      }
    });
  }
}
