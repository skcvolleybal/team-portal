import { Component, OnInit } from '@angular/core';
import { RequestService } from '../services/RequestService';

@Component({
  selector: 'app-wedstrijd-overzicht',
  templateUrl: './wedstrijd-overzicht.component.html',
  styleUrls: ['./wedstrijd-overzicht.component.css']
})
export class WedstrijdOverzichtComponent implements OnInit {
  wedstrijden: any[];
  loading: boolean;
  errorMessage: string;

  constructor(private requestService: RequestService) {}

  UpdateAanwezigheid(matchId, speler, aanwezigheid) {
    this.requestService
      .UpdateAanwezigheid(matchId, speler.id, aanwezigheid)
      .subscribe(() => {
        this.wedstrijden.forEach(wedstrijd => {
          if (wedstrijd.id === matchId) {
            if (
              wedstrijd.aanwezigen.find(
                aanwezige => aanwezige.id === speler.id
              ) &&
              aanwezigheid === 'Ja'
            ) {
              const newSpeler = {
                id: speler.id,
                naam: speler.naam,
                isInvaller: true
              };
              wedstrijd.aanwezigen.push(newSpeler);
            } else if (aanwezigheid === 'Nee') {
              wedstrijd.aanwezigen = wedstrijd.aanwezigen.filter(
                aanwezige => aanwezige.id !== speler.id
              );
            }

            return;
          }
        });
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
