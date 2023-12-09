import { HTTP_INTERCEPTORS, HttpClientModule } from '@angular/common/http';

import { AanwezigheidService } from './core/services/aanwezigheid.service';
import { AppComponent } from './app.component';
import { BarcieModule } from './barcie/barcie.module';
import { BarcieService } from './core/services/barcie.service';
import { BeschikbaarheidModule } from './beschikbaarheid/beschikbaarheid.module';
import { BeschikbaarheidService } from './core/services/beschikbaarheid.service';
import { BrowserModule } from '@angular/platform-browser';
import { CoreModule } from './core/core.module';
import { DefaultHeadersInterceptor } from './core/interceptors/default-headers.interceptor';
import { FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import { faCoffee } from '@fortawesome/free-solid-svg-icons';

import { HTTPListener } from './core/interceptors/is-authorized.interceptor';
import { ImpersonationInterceptor } from './core/interceptors/add-impersonation.interceptor';
import { WordPressService } from './core/services/request.service';
import { LoginModalComponent } from './login-modal/login-modal.component';
import { MijnOverzichtModule } from './mijn-overzicht/mijn-overzicht.module';
import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';
import { TeamtakencoModule } from './teamtakenco/teamtakenco.module';
import { TeamtakencoService } from './core/services/teamtakenco.service';
import { SharedModule } from './shared/shared.module';
import { StateService } from './core/services/state.service';
import { WedstrijdOverzichtModule } from './wedstrijd-overzicht/wedstrijd-overzicht.module';
import { WithCredentialsInterceptor } from './core/interceptors/add-credentials.interceptor';
import { appRoutes } from './route.config';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';

import { ToastrModule } from 'ngx-toastr';


@NgModule({
    declarations: [AppComponent, LoginModalComponent],
    imports: [
        HttpClientModule,
        RouterModule.forRoot(appRoutes, {
    useHash: true
}),
        BrowserModule,
        MijnOverzichtModule,
        WedstrijdOverzichtModule,
        BarcieModule,
        BeschikbaarheidModule,
        CoreModule,
        TeamtakencoModule,
        SharedModule,
        NgbModule,
        FontAwesomeModule,
        BrowserAnimationsModule, // required animations module
        ToastrModule.forRoot(), // ToastrModule added
    ],
    exports: [],
    providers: [
        StateService,
        WordPressService,
        AanwezigheidService,
        BarcieService,
        BeschikbaarheidService,
        TeamtakencoService,    
        {
            provide: HTTP_INTERCEPTORS,
            useClass: HTTPListener,
            multi: true,
        },
        {
            provide: HTTP_INTERCEPTORS,
            useClass: ImpersonationInterceptor,
            multi: true,
        },
        {
            provide: HTTP_INTERCEPTORS,
            useClass: DefaultHeadersInterceptor,
            multi: true,
        },
        {
            provide: HTTP_INTERCEPTORS,
            useClass: WithCredentialsInterceptor,
            multi: true,
        },
    ],
    bootstrap: [AppComponent]
})
export class AppModule {}
