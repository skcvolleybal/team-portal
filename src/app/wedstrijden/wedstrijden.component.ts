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

  constructor(private http: HttpClient) {}

  getWedstrijdAanwezigheid(): Observable<any[]> {
    return this.http.get<any[]>(
      environment.baseUrl + 'php/interface.php?action=GetWedstrijdAanwezigheid'
    );
  }

  updateAanwezigheid(aanwezigheid, match) {
    this.http
      .post<any>(
        environment.baseUrl + 'php/interface.php?action=UpdateAanwezigheid',
        {
          matchId: match.id,
          aanwezigheid
        }
      )
      .subscribe();
  }

  ngOnInit() {
    this.loading = true;
    this.getWedstrijdAanwezigheid().subscribe(
      wedstrijden => {
        this.wedstrijden = wedstrijden;
      },
      () => {},
      () => (this.loading = false)
    );
  }
}
