import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';

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

  GetCurrentUser(): Observable<any[]> {
    const url = environment.baseUrl + 'wordpress/user';
    return this.httpClient.get<any[]>(url);
  }
}
