import { HttpClient } from '@angular/common/http';
import { Component, Input, OnInit } from '@angular/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
// tslint:disable-next-line:no-implicit-dependencies
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-selecteer-zaalwacht',
  templateUrl: './selecteer-zaalwacht.component.html',
  styleUrls: ['./selecteer-zaalwacht.component.scss']
})
export class SelecteerZaalwachtComponent implements OnInit {
  constructor(public modal: NgbActiveModal, private httpClient: HttpClient) {}

  static date: string;
  static datum: string;

  spelendeTeams = [];
  overigeTeams = [];

  datum: string;
  date: string;
  zaalwachtoptiesLoading: boolean;
  errorMessage: string;

  ngOnInit() {
    this.datum = SelecteerZaalwachtComponent.datum;
    this.date = SelecteerZaalwachtComponent.date;
    this.getZaalwachtOpties(this.date);
  }

  getZaalwachtOpties(date) {
    this.zaalwachtoptiesLoading = true;

    this.httpClient
      .post<any>(
        environment.baseUrl,
        { date },
        {
          params: {
            action: 'GetZaalwachtTeams'
          }
        }
      )
      .subscribe(
        zaalwachtopties => {
          this.spelendeTeams = zaalwachtopties.spelendeTeams;
          this.overigeTeams = zaalwachtopties.overigeTeams;
          this.zaalwachtoptiesLoading = false;
        },
        error => {
          if (error.status === 500) {
            this.errorMessage = error.error;
            this.zaalwachtoptiesLoading = false;
          }
        }
      );
  }

  UpdateZaalwacht(team) {
    this.httpClient
      .post<any>(
        environment.baseUrl,
        {
          date: this.date,
          team
        },
        {
          params: {
            action: 'UpdateZaalwacht'
          }
        }
      )
      .subscribe(() => this.modal.close(team));
  }
}
