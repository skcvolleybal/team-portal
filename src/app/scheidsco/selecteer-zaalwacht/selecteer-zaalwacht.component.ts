import { Component, OnInit } from '@angular/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { ScheidscoService } from '../../core/services/scheidsco.service';

@Component({
  selector: 'teamportal-selecteer-zaalwacht',
  templateUrl: './selecteer-zaalwacht.component.html',
  styleUrls: ['./selecteer-zaalwacht.component.scss']
})
export class SelecteerZaalwachtComponent implements OnInit {
  constructor(
    public modal: NgbActiveModal,
    private scheidscoService: ScheidscoService
  ) {}

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

    this.scheidscoService.GetZaalwachtOpties(date).subscribe(
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
    this.scheidscoService
      .UpdateZaalwacht(this.date, team)
      .subscribe(() => this.modal.close(team));
  }
}
