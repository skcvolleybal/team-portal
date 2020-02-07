import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class StatistiekService {
  constructor(private httpClient: HttpClient) {}

  GetGespeeldePunten() {
    const url = environment.baseUrl + 'dwf/gespeelde-punten';
    return this.httpClient.get<any[]>(url);
  }

  GetStatistieken(matchId: string = ''): Observable<any> {
    const url = environment.baseUrl + 'statistieken/wedstrijden';

    return this.httpClient.get<any[]>(url, {
      params: {
        matchId
      }
    });
  }

  GetEigenWedstrijden(): Observable<any[]> {
    const url = environment.baseUrl + 'dwf/eigen-wedstrijden';
    return this.httpClient.get<any[]>(url);
  }
}
