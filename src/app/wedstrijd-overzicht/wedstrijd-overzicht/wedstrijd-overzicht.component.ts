import { Component, OnInit } from '@angular/core';

import { AanwezigheidService } from '../../core/services/aanwezigheid.service';
import { WordPressService } from '../../core/services/request.service';

@Component({
  selector: 'teamportal-wedstrijd-overzicht',
  templateUrl: './wedstrijd-overzicht.component.html',
  styleUrls: ['./wedstrijd-overzicht.component.scss'],
})
export class WedstrijdOverzichtComponent implements OnInit {
  wedstrijden: any[];
  loading: boolean;
  errorMessage: string;
  user: any;
  wedstrijdenEmpty: boolean = false;

  SortTeam = (speler1, speler2) => (speler1.naam > speler2.naam ? 1 : -1);

  constructor(
    private aanwezigheidService: AanwezigheidService,
    private wordPressService: WordPressService
  ) {}

  ngOnInit() {
    this.loading = true;
    this.wordPressService.GetWedstrijdOverzicht().subscribe(
      (wedstrijden) => {
        this.wedstrijden = wedstrijden;
        this.loading = false;
        // If wedstrijden is empty we display some text so that the user knows there is not an error.
        if (this.wedstrijden.length == 0) {
          this.wedstrijdenEmpty = true;
        }
      },
      (error) => {
        if (error.status === 500) {
          this.errorMessage = error.error.message;
          this.loading = false;
        }
      }
    );

    this.wordPressService.GetCurrentUser().subscribe((data) => {
      this.user = data;
    });
  }

  UpdateAanwezigheid(currentWedstrijd: any, isAanwezig: boolean, speler: any) {
    const matchId = currentWedstrijd.matchId;
    const rol =
      speler.rol ?? (currentWedstrijd.isEigenWedstrijd ? 'speler' : 'coach');
    this.aanwezigheidService.UpdateAanwezigheid(
      currentWedstrijd.matchId,
      isAanwezig,
      speler.id,
      rol
    );

    this.wedstrijden.forEach((wedstrijd) => {
      if (wedstrijd.matchId === matchId) {
        wedstrijd.afwezigen = wedstrijd.afwezigen.filter(
          (afwezige) => afwezige.id !== speler.id
        );
        wedstrijd.aanwezigen = wedstrijd.aanwezigen.filter(
          (aanwezige) => aanwezige.id !== speler.id
        );
        wedstrijd.onbekend = wedstrijd.onbekend.filter(
          (onbekende) => onbekende.id !== speler.id
        );

        if (rol === 'invaller' && isAanwezig === null) {
          return;
        }

        const newSpeler = {
          id: speler.id,
          naam: speler.naam,
          rol,
        };

        switch (isAanwezig) {
          case true:
            wedstrijd.aanwezigen.push(newSpeler);
            wedstrijd.aanwezigen.sort(this.SortTeam);
            break;
          case false:
            wedstrijd.afwezigen.push(newSpeler);
            wedstrijd.afwezigen.sort(this.SortTeam);
            break;
          case null:
            wedstrijd.onbekend.push(newSpeler);
            wedstrijd.onbekend.sort(this.SortTeam);
            break;
        }

        return;
      }
    });
  }
}
