 import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
 
@Injectable({
  providedIn: 'root',
})
export class TeamtakencoService {
  constructor(private httpClient: HttpClient) {}

  GetTeamtakencoOverzicht(): Observable<any> {
    return this.httpClient.get<any[]>(
      environment.baseUrl + 'teamtakenco/overzicht'
    );
  }

  GetScheidsrechtersForMatch(matchId: string): Observable<any> {
    return this.httpClient.get<any>(
      environment.baseUrl + 'teamtakenco/scheidsrechters',
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
      environment.baseUrl + 'teamtakenco/scheidsrechters',
      {
        matchId,
        scheidsrechterId,
      }
    );
  }

  GetTelTeams(matchId: string) {
    return this.httpClient.get<any>(environment.baseUrl + 'teamtakenco/tellers', {
      params: {
        matchId,
      },
    });
  }

  UpdateTellers(matchId: string, tellerId: number, tellerIndex: number) {
    return this.httpClient.post<any>(
      environment.baseUrl + 'teamtakenco/tellers',
      {
        matchId,
        tellerId,
        tellerIndex,
      }
    );
  }

  GetZaalwachtOpties(date: string) {
    return this.httpClient.get<any>(
      environment.baseUrl + 'teamtakenco/zaalwachtteams',
      {
        params: {
          date,
        },
      }
    );
  }

  UpdateZaalwacht(date: string, team: string, zaalwachttype: string) {
    return this.httpClient.post<any>(
      environment.baseUrl + 'teamtakenco/zaalwacht',
      {
        date,
        team,
        zaalwachttype,
      }
    );
  }
}
