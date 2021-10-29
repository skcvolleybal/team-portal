import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { environment } from '../../../environments/environment';

@Injectable({
  providedIn: 'root',
})
export class BeschikbaarheidService {
  constructor(private httpClient: HttpClient) {}
  UpdateFluitBeschikbaarheid(
    datum: string,
    tijd: string,
    isBeschikbaar: boolean
  ) {
    return this.httpClient.post(environment.baseUrl + 'fluiten', {
      datum,
      tijd,
      isBeschikbaar,
    });
  }

  UpdateBarcieBeschikbaarheid(date: string, isBeschikbaar: boolean) {
    return this.httpClient.post(environment.baseUrl + 'barcie', {
      date,
      isBeschikbaar,
    });
  }

  GetFluitBeschikbaarheid() {
    return this.httpClient.get<any[]>(environment.baseUrl + 'fluiten');
  }

  GetBarcieBeschikbaarheid() {
    return this.httpClient.get<any[]>(environment.baseUrl + 'barcie');
  }
}
