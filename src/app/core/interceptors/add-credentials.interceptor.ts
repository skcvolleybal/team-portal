import {
    HttpEvent,
    HttpHandler,
    HttpInterceptor,
    HttpRequest,
} from "@angular/common/http";

import { Injectable } from "@angular/core";
import { Observable } from "rxjs";

@Injectable()
export class WithCredentialsInterceptor implements HttpInterceptor {
    constructor() {}

    intercept(
        request: HttpRequest<any>,
        next: HttpHandler,
    ): Observable<HttpEvent<any>> {
        request = request.clone({
            withCredentials: true,
        });

        return next.handle(request);
    }
}
