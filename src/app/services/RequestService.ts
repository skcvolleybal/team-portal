import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from 'src/environments/environment';

@Injectable()
export class RequestService {
  constructor(private httpClient: HttpClient) {}

  GetGroupsOfUser() {
    return this.httpClient.get<boolean>(environment.baseUrl, {
      params: { action: 'GetGroups' }
    });
  }

  GetUsers(naam) {
    return this.httpClient.post(
      environment.baseUrl,
      {
        naam
      },
      {
        params: {
          action: 'GetUsers'
        }
      }
    );
  }

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

  Login(username: string, password: string) {
    return this.httpClient.post<any>(
      environment.baseUrl,
      {
        username,
        password
      },
      {
        params: {
          action: 'Login'
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

  AddBarcieDag(date: any) {
    return this.httpClient.post<any>(
      environment.baseUrl,
      {
        date: `${date.year}-${date.month}-${date.day}`
      },
      {
        params: {
          action: 'AddBarcieDag'
        }
      }
    );
  }

  DeleteBarcieDag(date: string) {
    return this.httpClient.post<any>(
      environment.baseUrl,
      {
        date
      },
      {
        params: {
          action: 'DeleteBarcieDag'
        }
      }
    );
  }

  ToggleBhv(date: string, naam: string, shift: string) {
    return this.httpClient.post<any>(
      environment.baseUrl,
      {
        date,
        naam,
        shift
      },
      {
        params: {
          action: 'ToggleBhv'
        }
      }
    );
  }

  DeleteBarcieAanwezigheid(date: string, naam: string, shift: string) {
    return this.httpClient.post<any>(
      environment.baseUrl,
      {
        date,
        naam,
        shift
      },
      {
        params: {
          action: 'DeleteBarcieAanwezigheid'
        }
      }
    );
  }

  GetBarcieleden(date: string) {
    return this.httpClient.get<any>(environment.baseUrl, {
      params: {
        action: 'GetBarcieLeden',
        date
      }
    });
  }

  AddBarcieAanwezigheid(date: string, shift: number, naam: string) {
    return this.httpClient.post<any>(
      environment.baseUrl,
      {
        date,
        shift,
        naam
      },
      {
        params: {
          action: 'AddBarcieAanwezigheid'
        }
      }
    );
  }

  GetBarcieRooster() {
    return this.httpClient.get<any>(environment.baseUrl, {
      params: {
        action: 'GetBarcieRooster'
      }
    });
  }

  UpdateBarcieBeschikbaarheid(date: string, beschikbaarheid: string) {
    return this.httpClient.post(
      environment.baseUrl,
      {
        date,
        beschikbaarheid
      },
      {
        params: { action: 'UpdateBarcieBeschikbaarheid' }
      }
    );
  }

  GetBarcieBeschikbaarheid() {
    return this.httpClient.get<any[]>(environment.baseUrl, {
      params: {
        action: 'GetBarcieBeschikbaarheid'
      }
    });
  }

  UpdateCoachAanwezigheid(matchId: string, aanwezigheid: string) {
    return this.httpClient.post(
      environment.baseUrl,
      {
        matchId,
        aanwezigheid
      },
      {
        params: { action: 'UpdateCoachAanwezigheid' }
      }
    );
  }

  GetCoachAanwezigheid() {
    return this.httpClient.get<any>(environment.baseUrl, {
      params: {
        action: 'GetCoachAanwezigheid'
      }
    });
  }

  UpdateFluitBeschikbaarheid(
    datum: string,
    tijd: string,
    beschikbaarheid: string
  ) {
    return this.httpClient.post(
      environment.baseUrl,
      {
        datum,
        tijd,
        beschikbaarheid
      },
      {
        params: { action: 'UpdateFluitBeschikbaarheid' }
      }
    );
  }

  GetFluitBeschikbaarheid() {
    return this.httpClient.get<any[]>(environment.baseUrl, {
      params: {
        action: 'GetFluitOverzicht'
      }
    });
  }

  GetMijnOverzicht() {
    return this.httpClient.get<any>(environment.baseUrl, {
      params: {
        action: 'GetMijnOverzicht'
      }
    });
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

  GetGespeeldePunten() {
    return this.httpClient.get<any[]>(environment.baseUrl, {
      params: {
        action: 'GetGespeeldePunten'
      }
    });
  }

  GetWedstrijdOverzicht(): Observable<any[]> {
    return this.httpClient.get<any[]>(environment.baseUrl, {
      params: {
        action: 'GetWedstrijdOverzicht'
      }
    });
  }

  UpdateAanwezigheid(matchId: string, spelerId: string, aanwezigheid: string) {
    return this.httpClient.post<any>(
      environment.baseUrl,
      {
        matchId,
        spelerId,
        aanwezigheid
      },
      {
        params: {
          action: 'UpdateAanwezigheid'
        }
      }
    );
  }

  GetWedstrijdAanwezigheid() {
    return this.httpClient.get<any[]>(environment.baseUrl, {
      params: {
        action: 'GetWedstrijdAanwezigheid'
      }
    });
  }
}
