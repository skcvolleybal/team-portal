import {
  HttpEvent,
  HttpHandler,
  HttpInterceptor,
  HttpRequest,
} from '@angular/common/http';

import { Injectable } from '@angular/core';
import { Observable } from 'rxjs/internal/Observable';
import { StateService } from '../services/state.service';
import { tap } from 'rxjs/operators';

// tslint:disable-next-line:max-classes-per-file
@Injectable()
export class HTTPListener implements HttpInterceptor {
  constructor(private stateService: StateService) {}

  intercept(
    request: HttpRequest<any>,
    next: HttpHandler
  ): Observable<HttpEvent<any>> {
    return next.handle(request).pipe(
      tap(
        (event) => {},
        (error) => {
          if (error.status === 401) {
            this.stateService.setIsAuthenticated(false);
          }
        }
      )
    );
  }
}
