import {
  HttpEvent,
  HttpHandler,
  HttpInterceptor,
  HttpRequest
} from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { tap } from 'rxjs/operators';
import { AuthenticationService } from '../services/authentication.service';

// tslint:disable-next-line:max-classes-per-file
@Injectable()
export class HTTPListener implements HttpInterceptor {
  constructor(private authenticationService: AuthenticationService) {}

  intercept(
    request: HttpRequest<any>,
    next: HttpHandler
  ): Observable<HttpEvent<any>> {
    return next.handle(request).pipe(
      tap(
        event => {},
        error => {
          if (error.status === 401) {
            this.authenticationService.setUnauthorized();
          }
        }
      )
    );
  }
}
