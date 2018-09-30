import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';

@Injectable()
export class AuthenticationService {
  private requestInFlight: BehaviorSubject<boolean>;
  constructor() {
    this.requestInFlight = new BehaviorSubject(false);
  }

  setUnauthorized(inFlight: boolean) {
    this.requestInFlight.next(inFlight);
  }

  getUnauthorized(): Observable<boolean> {
    return this.requestInFlight.asObservable();
  }
}
