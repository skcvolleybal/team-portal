import {
  HttpClient,
  HttpEvent,
  HttpHandler,
  HttpRequest
  // tslint:disable-next-line:no-submodule-imports
} from '@angular/common/http';
import { Component, Injectable, Injector, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { faAngleRight } from '@fortawesome/free-solid-svg-icons';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { AuthenticationService } from './services/authentication.service';
import { LoginModalComponent } from './login-modal/login-modal.component';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent implements OnInit {
  isNavbarHidden = true;
  rightIcon = faAngleRight;
  appRoutes;
  loginModal = false;

  constructor(
    private activatedRoute: ActivatedRoute,
    private injector: Injector,
    private modalService: NgbModal,
    private http: HttpClient,
    private authenticationService: AuthenticationService
  ) {
    this.appRoutes = this.injector
      .get('appRoutes')
      .filter(appRoute => appRoute.path !== '');
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

  login(username, password) {
    this.http
      .post<any>(
        'https://www.skcvolleybal.nl/scripts/team-portal/php/interface.php?action=Login',
        {
          username,
          password
        },
        {
          withCredentials: true
        }
      )
      .subscribe();
  }

  open() {
    if (!this.loginModal) {
      this.loginModal = true;
      this.modalService.open(LoginModalComponent);
    }
  }

  ngOnInit() {
    this.authenticationService
      .getUnauthorized()
      .subscribe((status: boolean) => {
        setTimeout(() => this.open());
      });
  }
}
