import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { environment } from '../../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class AanwezigheidService {
  constructor(private httpClient: HttpClient) {}

  UpdateCoachAanwezigheid(matchId: number, isAanwezig: string) {
    this.httpClient
      .post(
        environment.baseUrl,
        {
          matchId,
          isAanwezig
        },
        {
          params: { action: 'UpdateCoachAanwezigheid' }
        }
      )
      .subscribe();
  }

  GetCoachAanwezigheid() {
    return this.httpClient.get<any>(environment.baseUrl, {
      params: {
        action: 'GetCoachAanwezigheid'
      }
    });
  }

  UpdateAanwezigheid(
    matchId: number,
    isAanwezig: string,
    spelerId: string,
    rol: string
  ) {
    this.httpClient
      .post<any>(
        environment.baseUrl,
        {
          matchId,
          spelerId,
          isAanwezig,
          rol
        },
        {
          params: {
            action: 'UpdateAanwezigheid'
          }
        }
      )
      .subscribe();
  }

  GetWedstrijdAanwezigheid() {
    return this.httpClient.get<any[]>(environment.baseUrl, {
      params: {
        action: 'GetWedstrijdAanwezigheid'
      }
    });
  }

  DeleteBarcieAanwezigheid(date: string, shift: string, barcielidId: string) {
    return this.httpClient.post<any>(
      environment.baseUrl,
      {
        date,
        barcielidId,
        shift
      },
      {
        params: {
          action: 'DeleteBarcieAanwezigheid'
        }
      }
    );
  }

  AddBarcieAanwezigheid(date: string, shift: number, barcielidId: string) {
    return this.httpClient.post<any>(
      environment.baseUrl,
      {
        date,
        shift,
        barcielidId
      },
      {
        params: {
          action: 'AddBarcieAanwezigheid'
        }
      }
    );
  }
}
