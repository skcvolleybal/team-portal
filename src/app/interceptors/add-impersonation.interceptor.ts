import { HttpEvent, HttpHandler, HttpRequest } from '@angular/common/http';
import { HttpInterceptor } from '@angular/common/http/src/interceptor';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs/internal/Observable';
import { StateService } from '../services/state.service';

@Injectable()
export class ImpersonationInterceptor implements HttpInterceptor {
  constructor(private stateService: StateService) {}

  intercept(
    request: HttpRequest<any>,
    next: HttpHandler
  ): Observable<HttpEvent<any>> {
    if (this.stateService.isWebcie && this.stateService.impersonationId) {
      request = request.clone({
        setParams: {
          impersonationId: this.stateService.impersonationId
        }
      });
    }

    return next.handle(request);
  }
}
