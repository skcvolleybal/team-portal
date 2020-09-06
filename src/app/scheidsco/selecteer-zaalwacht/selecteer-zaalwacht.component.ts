import { Component, OnInit } from '@angular/core';

import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { ScheidscoService } from '../../core/services/scheidsco.service';
import { Team } from 'src/app/models/Team';

@Component({
  selector: 'teamportal-selecteer-zaalwacht',
  templateUrl: './selecteer-zaalwacht.component.html',
  styleUrls: ['./selecteer-zaalwacht.component.scss'],
})
export class SelecteerZaalwachtComponent implements OnInit {
  constructor(
    public modal: NgbActiveModal,
    private scheidscoService: ScheidscoService
  ) {}

  static date: string;
  static datum: string;
  static zaalwachttype: string;

  spelendeTeams = [];
  overigeTeams = [];

  datum: string;
  date: string;
  zaalwachttype: string;
  zaalwachtoptiesLoading: boolean;
  errorMessage: string;

  ngOnInit() {
    this.datum = SelecteerZaalwachtComponent.datum;
    this.date = SelecteerZaalwachtComponent.date;
    this.zaalwachttype = SelecteerZaalwachtComponent.zaalwachttype;
    this.getZaalwachtOpties(this.date);
  }

  getZaalwachtOpties(date) {
    this.zaalwachtoptiesLoading = true;

    this.scheidscoService.GetZaalwachtOpties(date).subscribe(
      (zaalwachtopties) => {
        this.spelendeTeams = zaalwachtopties.spelendeTeams;
        this.overigeTeams = zaalwachtopties.overigeTeams;
        this.zaalwachtoptiesLoading = false;
      },
      (error) => {
        if (error.status === 500) {
          this.errorMessage = error.error.message;
          this.zaalwachtoptiesLoading = false;
        }
      }
    );
  }

  UpdateZaalwacht(team: string) {
    this.scheidscoService
      .UpdateZaalwacht(this.date, team, this.zaalwachttype)
      .subscribe(() => this.modal.close(team));
  }
}
