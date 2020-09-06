import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { faAngleRight } from '@fortawesome/free-solid-svg-icons';
import { NgbModal, NgbModalConfig } from '@ng-bootstrap/ng-bootstrap';
import { Observable } from 'rxjs';
import { debounceTime, distinctUntilChanged, switchMap } from 'rxjs/operators';
import { JoomlaService } from './core/services/request.service';
import { StateService } from './core/services/state.service';
import { LoginModalComponent } from './login-modal/login-modal.component';
import { appRoutes } from './route.config';

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
  }

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

    this.joomalService.GetGroupsOfUser().subscribe((response) => {
      this.ShowMenuItems(response);
      this.isWebcie = response.findIndex((group) => group === 'webcie') !== -1;
      this.stateService.isWebcie = this.isWebcie;
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
