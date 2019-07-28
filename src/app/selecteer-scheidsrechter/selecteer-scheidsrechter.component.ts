import { Component, OnInit } from '@angular/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { ScheidscoService } from '../services/scheidsco.service';

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
    private scheidscoService: ScheidscoService,
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

    this.scheidscoService.GetScheidsrechtersForMatch(matchId).subscribe(
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
    const result = [];
    if (scheidsrechter.niveau) {
      result.push(scheidsrechter.niveau);
    }
    result.push(`${scheidsrechter.naam} (${scheidsrechter.gefloten})`);
    if (scheidsrechter.eigenTijd) {
      result.push(scheidsrechter.eigenTijd);
    }
    return result.join(', ');
  }

  GetClass(scheidsrechter: any) {
    return {
      'btn-danger': scheidsrechter.isMogelijk === 'Nee',
      'btn-success': scheidsrechter.isMogelijk === 'Ja',
      'btn-warning': scheidsrechter.isMogelijk === 'Onbekend'
    };
  }

  GetRegularCasing(text) {
    return text
      .replace(/([A-Z])/g, ' $1')
      .replace(/^./, (str: string) => str.toUpperCase());
  }

  UpdateScheidsrechter(scheidsrechter) {
    this.scheidscoService
      .UpdateScheidsrechter(this.wedstrijd.id, scheidsrechter)
      .subscribe(() => {
        this.modal.close(scheidsrechter);
      });
  }
}
