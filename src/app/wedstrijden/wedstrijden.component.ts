import { HttpClient } from '@angular/common/http';
import { Component, OnInit } from '@angular/core';
import {
  faCheck,
  faQuestion,
  faTimes
} from '@fortawesome/free-solid-svg-icons';
import { Observable } from 'rxjs/internal/Observable';
// tslint:disable-next-line:no-implicit-dependencies
import { environment } from 'src/environments/environment';

@Component({
  templateUrl: './wedstrijden.component.html',
  styleUrls: ['./wedstrijden.component.scss']
})
export class WedstrijdenComponent implements OnInit {
  model1 = null;
  neeIcon = faTimes;
  misschienIcon = faQuestion;
  jaIcon = faCheck;
  wedstrijden: any[];
  loading: boolean;
  errorMessage: any;

  constructor(private http: HttpClient) {}

  getWedstrijdAanwezigheid() {
    this.http
      .get<any[]>(environment.baseUrl, {
        params: {
          action: 'GetWedstrijdAanwezigheid'
        }
      })
      .subscribe(
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

  updateAanwezigheid(aanwezigheid, match) {
    this.http
      .post<any>(
        environment.baseUrl,
        {
          matchId: match.id,
          aanwezigheid
        },
        {
          params: {
            action: 'UpdateAanwezigheid'
          }
        }
      )
      .subscribe();
  }

  ngOnInit() {
    this.loading = true;
    this.getWedstrijdAanwezigheid();
  }
}
