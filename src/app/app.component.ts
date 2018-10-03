import { Component, Injector, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { faAngleRight } from '@fortawesome/free-solid-svg-icons';
import { NgbModal, NgbModalConfig } from '@ng-bootstrap/ng-bootstrap';
import { LoginModalComponent } from './login-modal/login-modal.component';
import { AuthenticationService } from './services/authentication.service';

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
    private authenticationService: AuthenticationService,
    config: NgbModalConfig
  ) {
    config.backdrop = 'static';
    config.keyboard = false;

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

  ngOnInit() {
    this.authenticationService.isAuthorized.subscribe(() => {
      setTimeout(() => this.modalService.open(LoginModalComponent));
    });
  }
}
