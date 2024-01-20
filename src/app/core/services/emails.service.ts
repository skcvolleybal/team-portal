import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { environment } from '../../../environments/environment';
import { Observable } from 'rxjs';



@Injectable({
  providedIn: 'root',
})
export class EmailsService {
    
  constructor(private http: HttpClient) {}


  getEmails(): Observable<any> {
    return this.http.get(environment.baseUrl + 'emails');
  }



}
