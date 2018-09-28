import { Component, Injector } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { faAngleRight } from '@fortawesome/free-solid-svg-icons';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent {
  isNavbarHidden = true;
  rightIcon = faAngleRight;
  appRoutes;

  constructor(
    private activatedRoute: ActivatedRoute,
    private injector: Injector
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
}
