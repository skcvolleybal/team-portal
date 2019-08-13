import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class RequestService {
  constructor(private httpClient: HttpClient) {}

  GetGroupsOfUser() {
    return this.httpClient.get<boolean>(environment.baseUrl, {
      params: { action: 'GetGroups' }
    });
  }

  GetUsers(naam) {
    return this.httpClient.post(
      environment.baseUrl,
      {
        naam
      },
      {
        params: {
          action: 'GetUsers'
        }
      }
    );
  }

  Login(username: string, password: string) {
    return this.httpClient.post<any>(
      environment.baseUrl,
      {
        username,
        password
      },
      {
        params: {
          action: 'Login'
        }
      }
    );
  }

  GetMijnOverzicht() {
    return this.httpClient.get<any>(environment.baseUrl, {
      params: {
        action: 'GetMijnOverzicht'
      }
    });
  }

  GetWedstrijdOverzicht(): Observable<any[]> {
    return this.httpClient.get<any[]>(environment.baseUrl, {
      params: {
        action: 'GetWedstrijdOverzicht'
      }
    });
  }

  GetCurrentUser(): Observable<any[]> {
    return this.httpClient.get<any[]>(environment.baseUrl, {
      params: {
        action: 'GetCurrentUser'
      }
    });
  }
}
