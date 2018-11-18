import { HttpClient } from '@angular/common/http';
import { Component, OnInit } from '@angular/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
// tslint:disable-next-line:no-implicit-dependencies
import { environment } from 'src/environments/environment';

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
  spelendeScheidsrechters: any;
  overigeScheidsrechters: any;
  wedstrijd: any;
  teams: string;
  tijd: string;

  constructor(private httpClient: HttpClient, public modal: NgbActiveModal) {}

  ngOnInit() {
    this.wedstrijd = SelecteerScheidsrechterComponent.wedstrijd;
    this.teams = this.wedstrijd.teams;
    this.tijd = SelecteerScheidsrechterComponent.tijd;
    this.getScheidsrechterOpties(this.wedstrijd.id);
  }

  getScheidsrechterOpties(matchId) {
    this.scheidsrechterOptiesLoading = true;

    this.httpClient
      .post<any>(
        environment.baseUrl,
        { matchId },
        {
          params: { action: 'GetScheidsrechters' }
        }
      )
      .subscribe(
        result => {
          this.spelendeScheidsrechters = result.spelendeScheidsrechters;
          this.overigeScheidsrechters = result.overigeScheidsrechters;
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
      'btn-danger': scheidsrechter.isMogelijk === 'Onbekend',
      'btn-success': scheidsrechter.isMogelijk === 'Ja',
      'btn-warning': scheidsrechter.isMogelijk === 'Nee'
    };
  }

  UpdateScheidsrechter(scheidsrechter) {
    this.httpClient
      .post<any>(
        environment.baseUrl,
        {
          matchId: this.wedstrijd.id,
          scheidsrechter
        },
        {
          params: {
            action: 'UpdateScheidsrechter'
          }
        }
      )
      .subscribe(() => {
        this.modal.close(scheidsrechter);
      });
  }
}
