import { Injectable } from '@angular/core';
import { Subject } from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class StateService {
  public isAuthenticated = new Subject<boolean>();
  public isWebcie = false;
  public impersonationId: string;

  setIsAuthenticated(value: boolean) {
    this.isAuthenticated.next(value);
  }
}
