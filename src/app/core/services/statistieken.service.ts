import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
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
}
