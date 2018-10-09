import { Injectable } from '@angular/core';
import { Subject } from 'rxjs';

@Injectable()
export class StateService {
  public isAuthorized = new Subject<any>();
  public isWebcie = false;
  public impersonationId: string;

  setUnauthorized() {
    this.isAuthorized.next();
  }
}
