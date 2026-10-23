import { ApplicationConfig, importProvidersFrom } from "@angular/core";
import {
    provideHttpClient,
    withInterceptorsFromDi,
} from "@angular/common/http";
import { provideAnimations } from "@angular/platform-browser/animations";
import { provideRouter } from "@angular/router";
import { NgbModule } from "@ng-bootstrap/ng-bootstrap";
import { FontAwesomeModule } from "@fortawesome/angular-fontawesome";
import { MatDialogModule } from "@angular/material/dialog";
import { BrowserModule } from "@angular/platform-browser";
import { BrowserAnimationsModule } from "@angular/platform-browser/animations";
import { provideToastService } from "ngx-yet-another-toast-library";
import { appRoutes } from "./route.config";

export const appConfig: ApplicationConfig = {
    providers: [
        provideHttpClient(withInterceptorsFromDi()),
        provideAnimations(),
        provideRouter(appRoutes),
        importProvidersFrom(
            BrowserModule,
            NgbModule,
            FontAwesomeModule,
            MatDialogModule,
        ),
        provideToastService(),
    ],
};
