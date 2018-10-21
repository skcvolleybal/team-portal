import { HttpClient } from '@angular/common/http';
import { Component, Injector, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { faAngleRight } from '@fortawesome/free-solid-svg-icons';
import { NgbModal, NgbModalConfig } from '@ng-bootstrap/ng-bootstrap';
import { Observable, of } from 'rxjs';
import {
  catchError,
  debounceTime,
  distinctUntilChanged,
  switchMap
} from 'rxjs/operators';
// tslint:disable-next-line:no-implicit-dependencies
import { environment } from 'src/environments/environment';
import { LoginModalComponent } from './login-modal/login-modal.component';
import { StateService } from './services/state.service';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent implements OnInit {
  constructor(
    private activatedRoute: ActivatedRoute,
    private injector: Injector,
    private modalService: NgbModal,
    private stateService: StateService,
    private httpClient: HttpClient,
    config: NgbModalConfig
  ) {
    config.backdrop = 'static';
    config.keyboard = false;

    this.appRoutes = this.injector
      .get('appRoutes')
      .filter(appRoute => appRoute.path !== '');
  }

  isWebcie = false;
  isNavbarHidden = true;
  rightIcon = faAngleRight;
  appRoutes;
  loginModal = false;
  impersonatedUser: any;

  onLinkClick() {
    this.isNavbarHidden = true;
  }

  toggleNavbar() {
    this.isNavbarHidden = !this.isNavbarHidden;
  }

  GetNavigationTitle() {
    if (this.activatedRoute.firstChild) {
      return this.activatedRoute.firstChild.snapshot.data['title'];
    }
  }

  ngOnInit() {
    this.stateService.isAuthorized.subscribe(() => {
      setTimeout(() =>
        this.modalService.open(LoginModalComponent, { centered: true })
      );
    });

    this.httpClient
      .get<boolean>(environment.baseUrl, {
        params: { action: 'IsWebcie' }
      })
      .subscribe(response => {
        this.stateService.isWebcie = response;
        this.isWebcie = response;
      });
  }

  setImpersonation() {
    if (!this.impersonatedUser) {
      this.stateService.impersonationId = null;
    }
  }

  searchUsers = (text: Observable<string>) =>
    text.pipe(
      debounceTime(300),
      distinctUntilChanged(),
      switchMap(term =>
        this.httpClient.post(
          environment.baseUrl,
          {
            name: `${term}`
          },
          {
            params: {
              action: 'GetUsers'
            }
          }
        )
      )
      // tslint:disable-next-line:semicolon
    );

  formatter = (gebruiker: { id: string; naam: string }) => {
    this.stateService.impersonationId = gebruiker.id;
    return gebruiker.naam;
    // tslint:disable-next-line:semicolon
  };
}
