import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';

@Injectable({
  providedIn: 'root',
})
export class ScheidscoService {
  constructor(private httpClient: HttpClient) {}

  GetScheidscoOverzicht(): Observable<any> {
    return this.httpClient.get<any[]>(
      environment.baseUrl + 'scheidsco/overzicht'
    );
  }

  GetScheidsrechtersForMatch(matchId: string): Observable<any> {
    return this.httpClient.get<any>(
      environment.baseUrl + 'scheidsco/scheidsrechters',
      {
        params: { matchId },
      }
    );
  }

  UpdateScheidsrechter(
    matchId: string,
    scheidsrechterId: number
  ): Observable<any> {
    return this.httpClient.post<any>(
      environment.baseUrl + 'scheidsco/scheidsrechters',
      {
        matchId,
        scheidsrechterId,
      }
    );
  }

  GetTelTeams(matchId: string) {
    return this.httpClient.get<any>(environment.baseUrl + 'scheidsco/tellers', {
      params: {
        matchId,
      },
    });
  }

  UpdateTellers(matchId: string, tellerId: number, tellerIndex: number) {
    return this.httpClient.post<any>(
      environment.baseUrl + 'scheidsco/tellers',
      {
        matchId,
        tellerId,
        tellerIndex,
      }
    );
  }

  GetZaalwachtOpties(date: string) {
    return this.httpClient.get<any>(
      environment.baseUrl + 'scheidsco/zaalwachtteams',
      {
        params: {
          date,
        },
      }
    );
  }

  UpdateZaalwacht(date: string, team: string, zaalwachttype: string) {
    return this.httpClient.post<any>(
      environment.baseUrl + 'scheidsco/zaalwacht',
      {
        date,
        team,
        zaalwachttype,
      }
    );
  }
}
