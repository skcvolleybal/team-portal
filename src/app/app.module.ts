import { HTTP_INTERCEPTORS, HttpClientModule } from '@angular/common/http';
import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { RouterModule } from '@angular/router';
import { StoreModule } from '@ngrx/store';
import { AppComponent } from './app.component';
import { BarcoModule } from './barco/barco.module';
import { BeschikbaarheidModule } from './beschikbaarheid/beschikbaarheid.module';
import { CoreModule } from './core/core.module';
import { CustomInterceptor } from './core/interceptors/add-credentials.interceptor';
import { ImpersonationInterceptor } from './core/interceptors/add-impersonation.interceptor';
import { HTTPListener } from './core/interceptors/is-authorized.interceptor';
import { AanwezigheidService } from './core/services/aanwezigheid.service';
import { BarcoService } from './core/services/barco.service';
import { BeschikbaarheidService } from './core/services/beschikbaarheid.service';
import { RequestService } from './core/services/request.service';
import { ScheidscoService } from './core/services/scheidsco.service';
import { StateService } from './core/services/state.service';
import { StatistiekService } from './core/services/statistieken.service';
import { LoginModalComponent } from './login-modal/login-modal.component';
import { MijnOverzichtModule } from './mijn-overzicht/mijn-overzicht.module';
import { appRoutes } from './route.config';
import { ScheidscoModule } from './scheidsco/scheidsco.module';
import { SharedModule } from './shared/shared.module';
import { StatistiekModule } from './statistiek/statistiek.module';
import { WedstrijdOverzichtModule } from './wedstrijd-overzicht/wedstrijd-overzicht.module';

@NgModule({
  entryComponents: [LoginModalComponent],
  declarations: [AppComponent, LoginModalComponent],
  imports: [
    HttpClientModule,
    RouterModule.forRoot(appRoutes, {
      useHash: true
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
    StoreModule
  ],
  exports: [],
  providers: [
    StateService,
    RequestService,
    AanwezigheidService,
    BarcoService,
    BeschikbaarheidService,
    ScheidscoService,
    StatistiekService,
    {
      provide: HTTP_INTERCEPTORS,
      useClass: CustomInterceptor,
      multi: true
    },
    {
      provide: HTTP_INTERCEPTORS,
      useClass: HTTPListener,
      multi: true
    },
    {
      provide: HTTP_INTERCEPTORS,
      useClass: ImpersonationInterceptor,
      multi: true
    }
  ],
  bootstrap: [AppComponent]
})
export class AppModule {}
