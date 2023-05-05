import { HTTP_INTERCEPTORS, HttpClientModule } from '@angular/common/http';

import { AanwezigheidService } from './core/services/aanwezigheid.service';
import { AppComponent } from './app.component';
import { BarcoModule } from './barco/barco.module';
import { BarcoService } from './core/services/barco.service';
import { BeschikbaarheidModule } from './beschikbaarheid/beschikbaarheid.module';
import { BeschikbaarheidService } from './core/services/beschikbaarheid.service';
import { BrowserModule } from '@angular/platform-browser';
import { CoreModule } from './core/core.module';
import { DefaultHeadersInterceptor } from './core/interceptors/default-headers.interceptor';
import { FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import { HTTPListener } from './core/interceptors/is-authorized.interceptor';
import { ImpersonationInterceptor } from './core/interceptors/add-impersonation.interceptor';
import { JoomlaService } from './core/services/request.service';
import { LoginModalComponent } from './login-modal/login-modal.component';
import { MijnOverzichtModule } from './mijn-overzicht/mijn-overzicht.module';
import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';
import { ScheidscoModule } from './scheidsco/scheidsco.module';
import { ScheidscoService } from './core/services/scheidsco.service';
import { SharedModule } from './shared/shared.module';
import { StateService } from './core/services/state.service';
import { StatistiekModule } from './statistiek/statistiek.module';
import { StatistiekService } from './core/services/statistieken.service';
import { WedstrijdOverzichtModule } from './wedstrijd-overzicht/wedstrijd-overzicht.module';
import { WithCredentialsInterceptor } from './core/interceptors/add-credentials.interceptor';
import { appRoutes } from './route.config';

@NgModule({
    declarations: [AppComponent, LoginModalComponent],
    imports: [
        HttpClientModule,
        RouterModule.forRoot(appRoutes, {
            useHash: true,
            relativeLinkResolution: 'legacy',
        }),
        BrowserModule,
        MijnOverzichtModule,
        WedstrijdOverzichtModule,
        BarcoModule,
        BeschikbaarheidModule,
        CoreModule,
        ScheidscoModule,
        StatistiekModule,
        SharedModule,
        FontAwesomeModule,
    ],
    exports: [],
    providers: [
        StateService,
        JoomlaService,
        AanwezigheidService,
        BarcoService,
        BeschikbaarheidService,
        ScheidscoService,
        StatistiekService,
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
