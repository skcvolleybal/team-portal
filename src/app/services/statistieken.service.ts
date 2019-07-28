import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { environment } from '../../environments/environment';

@Injectable()
export class StatistiekService {
  constructor(private httpClient: HttpClient) {}

  GetGespeeldePunten() {
    return this.httpClient.get<any[]>(environment.baseUrl, {
      params: {
        action: 'GetGespeeldePunten'
      }
    });
  }
}
