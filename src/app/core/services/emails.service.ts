import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { environment } from '../../../environments/environment';
import { Observable } from 'rxjs';
import { Email } from 'src/app/models/Email';
import { of } from 'rxjs';
import { tap } from 'rxjs/operators';

@Injectable({
  providedIn: 'root',
})
export class EmailsService {
  private emails: any[] = null;
    
  constructor(private http: HttpClient) {}


  getEmails(): Observable<any> {
    if (this.emails) {
      return of(this.emails);
    } else {
        return this.http.get(environment.baseUrl + 'emails').pipe(
          tap(fetchedEmails => this.emails = fetchedEmails)
        );
    }
  }

  getEmailById(id: string): Observable<Email> {
    return this.http.get<Email>(environment.baseUrl + 'emails/' + id);
  }



}
