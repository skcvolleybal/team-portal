import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { forkJoin } from 'rxjs';
import { share } from 'rxjs/operators'

@Injectable({
  providedIn: 'root',
})
export class WordPressService {
  constructor(private httpClient: HttpClient) {}

  GetGroupsOfUser() {
    const url = environment.baseUrl + 'wordpress/groepen';
    return this.httpClient.get<string[]>(url);
  }

  GetUsers(naam) {
    const url = environment.baseUrl + 'wordpress/users';
    return this.httpClient.get(url, {
      params: {
        naam,
      },
    });
  }

  Login(username: string, password: string) {
    const url = environment.baseUrl + 'wordpress/inloggen';
    return this.httpClient.post<any>(url, {
      username,
      password,
    });
  }

  GetMijnOverzicht() {
    const url = environment.baseUrl + 'mijn-overzicht';
    return this.httpClient.get<any>(url);
  }

  GetWedstrijdOverzicht(): Observable<any[]> {
    const url = environment.baseUrl + 'wedstrijd-overzicht';
    return this.httpClient.get<any[]>(url);
  }

  GetAllBardiensten(): Observable<any[]> {
    const url = environment.baseUrl + 'diensten/bar';
    return this.httpClient.get<any[]>(url);
  }

  GetBarDienstenForUser(id: string): Observable<any[]> {
    const url = environment.baseUrl + `diensten/bar/${id}`;
    return this.httpClient.get<any[]>(url);
  }

  GetTelScheidsDienstenForUser(): Observable<any[]> {
    const url = environment.baseUrl + 'diensten/scheids';
    return this.httpClient.get<any[]>(url);
  }

  GetDienstenForUser(id: string): Observable<any[]> {
    const bar = this.GetBarDienstenForUser(id).pipe(share());
    const telfluit = this.GetTelScheidsDienstenForUser().pipe(share());
    return forkJoin([bar, telfluit ]);
  }

  GetCurrentUser(): Observable<any[]> {
    const url = environment.baseUrl + 'wordpress/user';
    return this.httpClient.get<any[]>(url);
  }

  GetWeekOverzicht(datum): Observable<any[]> {
    const url = environment.baseUrl + 'week-overzicht';
    return this.httpClient.get<any[]>(url, {
      responseType: 'blob' as 'json',
      params: {
        datum,
      },
    });
  }
}
