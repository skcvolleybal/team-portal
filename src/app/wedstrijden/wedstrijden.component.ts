// tslint:disable-next-line:no-submodule-imports
import { HttpClient } from '@angular/common/http';
import { Component, OnInit } from '@angular/core';
import {
  faCheck,
  faQuestion,
  faTimes
} from '@fortawesome/free-solid-svg-icons';
import { Observable } from 'rxjs/internal/Observable';

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

  constructor(private http: HttpClient) {}

  getWedstrijdAanwezigheid(): Observable<any[]> {
    return this.http.get<any[]>(
      'https://www.skcvolleybal.nl/scripts/team-portal/php/interface.php?action=GetWedstrijdAanwezigheid',
      {
        withCredentials: true
      }
    );
  }

  updateAanwezigheid(aanwezigheid, match) {
    this.http
      .post<any>(
        'https://www.skcvolleybal.nl/scripts/team-portal/php/interface.php?action=UpdateAanwezigheid',
        {
          matchId: match.id,
          aanwezigheid
        },
        {
          withCredentials: true
        }
      )
      .subscribe();
  }

  ngOnInit() {
    this.getWedstrijdAanwezigheid().subscribe(wedstrijden => {
      this.wedstrijden = wedstrijden;
    });
  }
}
