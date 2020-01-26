import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { environment } from '../../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class AanwezigheidService {
  constructor(private httpClient: HttpClient) {}

  UpdateAanwezigheid(
    matchId: number,
    isAanwezig: boolean,
    spelerId: string,
    rol: string
  ) {
    this.httpClient
      .post<any>(environment.baseUrl + 'wedstrijd-overzicht/aanwezigheid', {
        matchId,
        spelerId,
        isAanwezig,
        rol
      })
      .subscribe();
  }

  DeleteBarcieAanwezigheid(date: string, shift: string, barlidId: string) {
    return this.httpClient.delete<any>(
      environment.baseUrl + 'barco/dienst',
      {
        params: {
          date,
          barlidId,
          shift
        }
      }
    );
  }

  AddBarcieAanwezigheid(date: string, shift: number, barlidId: string) {
    return this.httpClient.post<any>(
      environment.baseUrl + 'barco/dienst',
      {
        date,
        shift,
        barlidId
      }
    );
  }
}
