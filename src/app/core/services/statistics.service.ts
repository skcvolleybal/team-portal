import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { environment } from '../../../environments/environment';
import { Observable } from 'rxjs';



@Injectable({
  providedIn: 'root',
})
export class StatisticsService {
    
  constructor(private http: HttpClient) {}


  getSkcRanking(): Observable<any> {
    return this.http.get( environment.baseUrl + 'statistics/getskcranking');
  }



}
