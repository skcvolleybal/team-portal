import { Component, OnInit } from '@angular/core';

import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { TeamtakencoService } from '../../core/services/teamtakenco.service';

@Component({
  selector: 'teamportal-selecteer-tellers',
  templateUrl: './selecteer-tellers.component.html',
  styleUrls: ['./selecteer-tellers.component.scss'],
})
export class SelecteerTellersComponent implements OnInit {
  static wedstrijd: any;
  static tijd: string;
  static tellerIndex: number;

  tellersOptiesLoading: boolean;
  spelendeTeams = [];
  overigeTeams = [];
  errorMessage: string;
  wedstrijd: any;
  teams: string;
  tijd: string;
  tellerIndex: number;

  constructor(
    public modal: NgbActiveModal,
    private teamtakencoService: TeamtakencoService
  ) {}

  ngOnInit() {
    this.wedstrijd = SelecteerTellersComponent.wedstrijd;
    this.tijd = SelecteerTellersComponent.tijd;
    this.tellerIndex = SelecteerTellersComponent.tellerIndex;
    this.teams = this.wedstrijd.teams;

    this.getTelTeams(this.wedstrijd.matchId);
  }

  getTelTeams(matchId) {
    this.tellersOptiesLoading = true;

    this.teamtakencoService.GetTelTeams(matchId).subscribe(
      (zaalwachtopties) => {
        this.spelendeTeams = zaalwachtopties.spelendeTeams;
        this.overigeTeams = zaalwachtopties.overigeTeams;
        this.tellersOptiesLoading = false;
      },
      (error) => {
        if (error.status === 500) {
          this.errorMessage = error.error.message;
          this.tellersOptiesLoading = false;
        }
      }
    );
  }

  UpdateTeller(teller) {
    const $this = this;
    this.teamtakencoService
      .UpdateTellers(this.wedstrijd.matchId, teller.id, this.tellerIndex)
      .subscribe(() =>{
        console.log('success')
        this.modal.close({ teller, tellerIndex: $this.tellerIndex })
      });
  }
}
