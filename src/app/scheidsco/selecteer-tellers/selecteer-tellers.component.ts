import { Component, OnInit } from '@angular/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { ScheidscoService } from '../../core/services/scheidsco.service';

@Component({
  selector: 'teamportal-selecteer-tellers',
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

  constructor(
    public modal: NgbActiveModal,
    private scheidscoService: ScheidscoService
  ) {}

  ngOnInit() {
    this.wedstrijd = SelecteerTellersComponent.wedstrijd;
    this.teams = this.wedstrijd.teams;
    this.tijd = SelecteerTellersComponent.tijd;
    this.getTelTeams(this.wedstrijd.matchId);
  }

  getTelTeams(matchId) {
    this.tellersOptiesLoading = true;

    this.scheidscoService.GetTelTeams(matchId).subscribe(
      zaalwachtopties => {
        this.spelendeTeams = zaalwachtopties.spelendeTeams;
        this.overigeTeams = zaalwachtopties.overigeTeams;
        this.tellersOptiesLoading = false;
      },
      error => {
        if (error.status === 500) {
          this.errorMessage = error.error.message;
          this.tellersOptiesLoading = false;
        }
      }
    );
  }

  UpdateTellers(tellers) {
    this.scheidscoService
      .UpdateTellers(this.wedstrijd.matchId, tellers)
      .subscribe(() => this.modal.close(tellers));
  }
}
