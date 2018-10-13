import { HttpClient } from '@angular/common/http';
import { Component, OnInit } from '@angular/core';
import { Observable } from 'rxjs/internal/Observable';
// tslint:disable-next-line:no-implicit-dependencies
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-wedstrijd-overzicht',
  templateUrl: './wedstrijd-overzicht.component.html',
  styleUrls: ['./wedstrijd-overzicht.component.css']
})
export class WedstrijdOverzichtComponent implements OnInit {
  wedstrijden: any[];
  loading: boolean;
  errorMessage: string;

  constructor(private httpClient: HttpClient) {}

  getWedstrijdOverzicht(): Observable<any[]> {
    return this.httpClient.get<any[]>(environment.baseUrl, {
      params: {
        action: 'GetWedstrijdOverzicht'
      }
    });
  }

  AddAanwezigheid(speler, matchId) {
    this.httpClient
      .post<any>(
        environment.baseUrl,
        {
          matchId,
          spelerId: speler.id,
          aanwezigheid: 'Ja'
        },
        {
          params: {
            action: 'UpdateAanwezigheid'
          }
        }
      )
      .subscribe(() => {
        this.wedstrijden.forEach(wedstrijd => {
          if (wedstrijd.id === matchId) {
            if (
              !wedstrijd.aanwezigen.find(
                aanwezige => aanwezige.id === speler.id
              )
            ) {
              const newSpeler = {
                id: speler.id,
                naam: speler.naam,
                isInvaller: true
              };
              wedstrijd.aanwezigen.push(newSpeler);
            }
            return;
          }
        });
      });
  }

  DeleteAanwezigheid(spelerId, matchId) {
    this.httpClient
      .post<any>(
        environment.baseUrl,
        {
          matchId,
          spelerId,
          aanwezigheid: 'Misschien'
        },
        {
          params: {
            action: 'UpdateAanwezigheid'
          }
        }
      )
      .subscribe(() => {
        this.wedstrijden.forEach(wedstrijd => {
          if (wedstrijd.id === matchId) {
            if (
              wedstrijd.aanwezigen.find(aanwezige => aanwezige.id === spelerId)
            ) {
              wedstrijd.aanwezigen = wedstrijd.aanwezigen.filter(
                aanwezige => aanwezige.id !== spelerId
              );
            }
            return;
          }
        });
      });
  }

  ngOnInit() {
    this.loading = true;
    this.getWedstrijdOverzicht().subscribe(
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
