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
    return this.httpClient.get<any[]>(environment.baseUrl, {
      params: {
        action: 'GetScheidscoOverzicht'
      }
    });
  }

  GetScheidsrechtersForMatch(matchId: string): Observable<any> {
    return this.httpClient.post<any>(
      environment.baseUrl,
      { matchId },
      {
        params: { action: 'GetScheidsrechters' }
      }
    );
  }

  UpdateScheidsrechter(
    matchId: string,
    scheidsrechter: string
  ): Observable<any> {
    return this.httpClient.post<any>(
      environment.baseUrl,
      {
        matchId,
        scheidsrechter
      },
      {
        params: {
          action: 'UpdateScheidsrechter'
        }
      }
    );
  }

  GetTelTeams(matchId: string) {
    return this.httpClient.post<any>(
      environment.baseUrl,
      { matchId },
      {
        params: {
          action: 'GetTelTeams'
        }
      }
    );
  }

  UpdateTellers(matchId: string, tellers: string) {
    return this.httpClient.post<any>(
      environment.baseUrl,
      {
        matchId,
        tellers
      },
      {
        params: {
          action: 'UpdateTellers'
        }
      }
    );
  }

  GetZaalwachtOpties(date: string) {
    return this.httpClient.post<any>(
      environment.baseUrl,
      { date },
      {
        params: {
          action: 'GetZaalwachtTeams'
        }
      }
    );
  }

  UpdateZaalwacht(date: string, team: string) {
    return this.httpClient.post<any>(
      environment.baseUrl,
      {
        date,
        team
      },
      {
        params: {
          action: 'UpdateZaalwacht'
        }
      }
    );
  }
}
