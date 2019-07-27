import { Component, OnInit } from '@angular/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { RequestService } from '../services/RequestService';

@Component({
  selector: 'app-selecteer-scheidsrechter',
  templateUrl: './selecteer-scheidsrechter.component.html',
  styleUrls: ['./selecteer-scheidsrechter.component.scss']
})
export class SelecteerScheidsrechterComponent implements OnInit {
  static wedstrijd: any;
  static tijd: string;

  scheidsrechterOptiesLoading: boolean;
  errorMessage: string;
  scheidsrechters: any[];

  scheidsrechtertypes = ['spelendeScheidsrechters', 'overigeScheidsrechters'];
  keuzes = ['Ja', 'Onbekend', 'Nee'];

  wedstrijd: any;
  teams: string;
  tijd: string;

  constructor(
    private requestService: RequestService,
    public modal: NgbActiveModal
  ) {}

  ngOnInit() {
    this.wedstrijd = SelecteerScheidsrechterComponent.wedstrijd;
    this.teams = this.wedstrijd.teams;
    this.tijd = SelecteerScheidsrechterComponent.tijd;
    this.getScheidsrechterOpties(this.wedstrijd.id);
  }

  getScheidsrechterOpties(matchId: string) {
    this.scheidsrechterOptiesLoading = true;

    this.requestService.GetScheidsrechtersForMatch(matchId).subscribe(
      result => {
        this.scheidsrechters = result;
        this.scheidsrechterOptiesLoading = false;
      },
      error => {
        if (error.status === 500) {
          this.errorMessage = error.error;
          this.scheidsrechterOptiesLoading = false;
        }
      }
    );
  }

  getScheidsrechtersByKeuze(scheidsrechterstype: string, keuze: string) {
    return this.scheidsrechters[scheidsrechterstype][keuze];
  }

  GetScheidsrechterText(scheidsrechter) {
    return `${scheidsrechter.niveau}, ${scheidsrechter.naam} (${
      scheidsrechter.gefloten
    }), ${scheidsrechter.eigenTijd}`;
  }

  GetScheidsrechterTextWithoutTime(scheidsrechter) {
    return `${scheidsrechter.niveau}, ${scheidsrechter.naam} (${
      scheidsrechter.gefloten
    })`;
  }

  GetClass(scheidsrechter) {
    return {
      'btn-danger': scheidsrechter.isMogelijk === 'Nee',
      'btn-success': scheidsrechter.isMogelijk === 'Ja',
      'btn-warning': scheidsrechter.isMogelijk === 'Onbekend'
    };
  }

  UpdateScheidsrechter(scheidsrechter) {
    this.requestService
      .UpdateScheidsrechter(this.wedstrijd.id, scheidsrechter)
      .subscribe(() => {
        this.modal.close(scheidsrechter);
      });
  }
}
