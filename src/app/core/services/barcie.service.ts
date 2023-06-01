import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { environment } from '../../../environments/environment';

@Injectable({
  providedIn: 'root',
})
export class BarcieService {
  constructor(private httpClient: HttpClient) {}

  AddBarcieDag(date: any) {
    return this.httpClient.post<any>(environment.baseUrl + 'barcie/dag', {
      date: `${date.year}-${date.month}-${date.day}`,
    });
  }

  DeleteBarcieDag(date: string) {
    return this.httpClient.delete<any>(environment.baseUrl + 'barcie/dag', {
      params: {
        date,
      },
    });
  }

  ToggleBhv(date: string, shift: string, barlidId: number): void {
    this.httpClient
      .post<any>(environment.baseUrl + 'barcie/toggle-bhv', {
        date,
        barlidId,
        shift,
      })
      .subscribe();
  }

  GetBarcieBeschikbaarheden(date: string) {
    return this.httpClient.get<any>(
      environment.baseUrl + 'barcie/beschikbaarheden',
      {
        params: {
          date,
        },
      }
    );
  }

  GetBarcieRooster() {
    return this.httpClient.get<any>(environment.baseUrl + 'barcie/rooster');
  }
}
