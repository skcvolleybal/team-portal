import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { environment } from '../../../environments/environment';

@Injectable({
  providedIn: 'root',
})
export class BarcoService {
  constructor(private httpClient: HttpClient) {}

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

  ToggleBhv(date: string, shift: string, barcielidId: number): void {
    this.httpClient
      .post<any>(
        environment.baseUrl,
        {
          date,
          barcielidId,
          shift
        },
        {
          params: {
            action: 'ToggleBhv'
          }
        }
      )
      .subscribe();
  }

  GetBarcieBeschikbaarheden(date: string) {
    return this.httpClient.get<any>(environment.baseUrl, {
      params: {
        action: 'GetBarcieBeschikbaarheden',
        date
      }
    });
  }

  GetBarcieRooster() {
    return this.httpClient.get<any>(environment.baseUrl, {
      params: {
        action: 'GetBarcieRooster'
      }
    });
  }
}
