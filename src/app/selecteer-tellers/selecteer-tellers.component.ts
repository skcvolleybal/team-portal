import { HttpClient } from '@angular/common/http';
import { Component, OnInit } from '@angular/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
// tslint:disable-next-line:no-implicit-dependencies
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-selecteer-tellers',
  templateUrl: './selecteer-tellers.component.html',
  styleUrls: ['./selecteer-tellers.component.scss']
})
export class SelecteerTellersComponent implements OnInit {
  static wedstrijd: any;
  static tijd: string;

  tellersOptiesLoading: boolean;
  spelendeTeams = [];
  overigeTeams = [];
  errorMessage: string;
  wedstrijd: any;
  teams: string;
  tijd: string;

  constructor(private httpClient: HttpClient, public modal: NgbActiveModal) {}

  ngOnInit() {
    this.wedstrijd = SelecteerTellersComponent.wedstrijd;
    this.teams = this.wedstrijd.teams;
    this.tijd = SelecteerTellersComponent.tijd;
    this.getTelTeams(this.wedstrijd.id);
  }

  getTelTeams(matchId) {
    this.tellersOptiesLoading = true;

    this.httpClient
      .post<any>(
        environment.baseUrl,
        { matchId },
        {
          params: {
            action: 'GetTelTeams'
          }
        }
      )
      .subscribe(
        zaalwachtopties => {
          this.spelendeTeams = zaalwachtopties.spelendeTeams;
          this.overigeTeams = zaalwachtopties.overigeTeams;
          this.tellersOptiesLoading = false;
        },
        error => {
          if (error.status === 500) {
            this.errorMessage = error.error;
            this.tellersOptiesLoading = false;
          }
        }
      );
  }

  UpdateTellers(tellers) {
    this.httpClient
      .post<any>(
        environment.baseUrl,
        {
          matchId: this.wedstrijd.id,
          tellers
        },
        {
          params: {
            action: 'UpdateTellers'
          }
        }
      )
      .subscribe(() => this.modal.close(tellers));
  }
}
