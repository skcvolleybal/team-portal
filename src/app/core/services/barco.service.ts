import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { environment } from '../../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class BarcoService {
  constructor(private httpClient: HttpClient) {}

  AddBarcieDag(date: any) {
    return this.httpClient.post<any>(
      environment.baseUrl + 'barcie/barciedag/add',
      {
        date: `${date.year}-${date.month}-${date.day}`
      }
    );
  }

  DeleteBarcieDag(date: string) {
    return this.httpClient.post<any>(
      environment.baseUrl + 'barcie/barciedag/delete',
      {
        date
      }
    );
  }

  ToggleBhv(date: string, shift: string, barcielidId: number): void {
    this.httpClient
      .post<any>( environment.baseUrl + 'barcie/toggle-bhv',
        {
          date,
          barcielidId,
          shift
        }
      )
      .subscribe();
  }

  GetBarcieBeschikbaarheden(date: string) {
    return this.httpClient.get<any>(environment.baseUrl + 'barcie/beschikbaarheden', {
      params: {
        date
      }
    });
  }

  GetBarcieRooster() {
    return this.httpClient.get<any>(environment.baseUrl + 'barcie/rooster');
  }
}
