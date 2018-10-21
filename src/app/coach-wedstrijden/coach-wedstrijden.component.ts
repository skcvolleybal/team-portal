import { HttpClient } from '@angular/common/http';
import { Component, OnInit } from '@angular/core';
import {
  faCalendarCheck,
  faCheck,
  faQuestion,
  faTimes,
  faUser
} from '@fortawesome/free-solid-svg-icons';
// tslint:disable-next-line:no-implicit-dependencies
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-coach-wedstrijden',
  templateUrl: './coach-wedstrijden.component.html',
  styleUrls: ['./coach-wedstrijden.component.scss']
})
export class CoachWedstrijdenComponent implements OnInit {
  neeIcon = faTimes;
  onbekendIcon = faQuestion;
  jaIcon = faCheck;
  scheidsrechterIcon = faUser;
  teamIcon = faCalendarCheck;

  loading: boolean;
  errorMessage: string;
  wedstrijden: any[];

  constructor(private httpClient: HttpClient) {}

  ngOnInit() {
    this.getCoachAanwezigheid();
  }

  UpdateCoachAanwezigheid(aanwezigheid, matchId) {
    this.httpClient
      .post(
        environment.baseUrl,
        {
          matchId,
          aanwezigheid
        },
        {
          params: { action: 'UpdateCoachAanwezigheid' }
        }
      )
      .subscribe();
  }

  getCoachAanwezigheid() {
    this.loading = true;
    this.httpClient
      .get<any>(environment.baseUrl, {
        params: {
          action: 'GetCoachAanwezigheid'
        }
      })
      .subscribe(
        response => {
          this.wedstrijden = response.wedstrijden;
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
}
