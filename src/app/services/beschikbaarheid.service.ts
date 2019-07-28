import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { environment } from '../../environments/environment';

@Injectable()
export class BeschikbaarheidService {
  constructor(private httpClient: HttpClient) {}
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

  GetFluitBeschikbaarheid() {
    return this.httpClient.get<any[]>(environment.baseUrl, {
      params: {
        action: 'GetFluitOverzicht'
      }
    });
  }

  GetBarcieBeschikbaarheid() {
    return this.httpClient.get<any[]>(environment.baseUrl, {
      params: {
        action: 'GetBarcieBeschikbaarheid'
      }
    });
  }
}
