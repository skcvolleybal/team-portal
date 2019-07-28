import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { environment } from 'src/environments/environment';

@Injectable()
export class AanwezigheidService {
  constructor(private httpClient: HttpClient) {}

  UpdateCoachAanwezigheid(matchId: string, aanwezigheid: string) {
    this.httpClient
      .post(
        environment.baseUrl,
        {
          matchId,
          aanwezigheid
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

  UpdateAanwezigheid(matchId: string, spelerId: string, aanwezigheid: string) {
    this.httpClient
      .post<any>(
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
