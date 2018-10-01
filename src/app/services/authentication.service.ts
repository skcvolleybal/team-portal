import { Injectable } from '@angular/core';
import { Subject } from 'rxjs';

@Injectable()
export class AuthenticationService {
  public isAuthorized = new Subject<any>();

  setUnauthorized() {
    this.isAuthorized.next();
  }
}
