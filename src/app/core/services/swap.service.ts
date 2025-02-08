import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { forkJoin } from 'rxjs';
import { share } from 'rxjs/operators'

@Injectable({
  providedIn: 'root',
})
export class SwapService {
  constructor(private httpClient: HttpClient) {}

  GetAllSwaps() {
    const url = environment.baseUrl + 'swaps';
    return this.httpClient.get<any[]>(url);
  }

  GetSwapsById(id) {
    const url = environment.baseUrl + `swaps/${id}`;
    return this.httpClient.get<any>(url)
  }

  GetProposedSwaps() {
    const url = environment.baseUrl + 'swaps/proposed';
    return this.httpClient.get<any[]>(url);
  }

  ProposeSwap(newSwap: any) {
    const url = environment.baseUrl + 'swaps';
    return this.httpClient.post<any>(url, {
        newSwap
    });
  }

  DeleteSwap(id: string) {
    return this.httpClient.delete<any>(environment.baseUrl + `swaps/${id}`, {
    });
  }

  AcceptSwap(acceptSwap: any) {
    return this.httpClient.post<any>(environment.baseUrl + `swaps/swaptaak`, {
      acceptSwap
    });
  }

}