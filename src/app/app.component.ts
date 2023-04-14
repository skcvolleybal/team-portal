import { Component, OnInit } from '@angular/core';
import { NgbModal, NgbModalConfig } from '@ng-bootstrap/ng-bootstrap';
import { debounceTime, distinctUntilChanged, switchMap } from 'rxjs/operators';

import { ActivatedRoute } from '@angular/router';
import { JoomlaService } from './core/services/request.service';
import { LoginModalComponent } from './login-modal/login-modal.component';
import { Observable } from 'rxjs';
import { StateService } from './core/services/state.service';
import { appRoutes } from './route.config';
import { faAngleRight } from '@fortawesome/free-solid-svg-icons';
import { environment } from '../environments/environment';


@Component({
  selector: 'teamportal-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css'],
})
export class AppComponent implements OnInit {
  isWebcie = false;
  isNavbarHidden = true;
  rightIcon = faAngleRight;
  loginModal = false;
  impersonatedUser: any;
  appRoutes: any;
  searching = false;
  searchFailed = false;
  isProd = false;

  constructor(
    private activatedRoute: ActivatedRoute,
    private modalService: NgbModal,
    private stateService: StateService,
    private joomalService: JoomlaService,
    config: NgbModalConfig
  ) {
    config.backdrop = 'static';
    config.keyboard = false;

    this.appRoutes = appRoutes.filter((appRoute) => appRoute.path !== '');

    if (environment.production) {
      this.isProd = true;
      }
    }


  onLinkClick() {
    this.isNavbarHidden = true;
  }

  toggleNavbar() {
    this.isNavbarHidden = !this.isNavbarHidden;
  }

  GetNavigationTitle() {
    if (this.activatedRoute.firstChild) {
      return this.activatedRoute.firstChild.snapshot.data.title;
    }
  }

  ngOnInit() {
    this.stateService.isAuthenticated.subscribe((value) => {
      if (value) {
        return;
      }

      this.modalService.open(LoginModalComponent, { centered: true });
    });

    this.joomalService.GetGroupsOfUser().subscribe((response) => {
      this.ShowMenuItems(response);
      this.isWebcie = response.findIndex((group) => group === 'webcie') !== -1;
      this.stateService.isWebcie = this.isWebcie;
    });

    this.stateService.isAuthenticated.subscribe((isAuthenticated) => {
      if (isAuthenticated) {
        this.ngOnInit();
      }
    });    
  }


  ShowMenuItems(groups) {
    groups.forEach((subscription) => {
      this.appRoutes.forEach((appRoute) => {
        if (appRoute.data.groups) {
          appRoute.data.groups.forEach((group) => {
            if (group === subscription) {
              appRoute.isHidden = false;
            }
          });
        }
      });
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
      switchMap((term) => this.joomalService.GetUsers(term))
      // tslint:disable-next-line:semicolon
    );

  formatter = (gebruiker: { id: string; naam: string }) => {
    this.stateService.impersonationId = gebruiker.id;
    return gebruiker.naam;
    // tslint:disable-next-line:semicolon
  };
}
