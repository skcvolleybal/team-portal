import { Component } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { faAngleRight } from '@fortawesome/free-solid-svg-icons';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent {
  constructor(private activatedRoute: ActivatedRoute) {}
  isNavbarHidden = true;

  rightIcon = faAngleRight;

  onLinkClick() {
    this.isNavbarHidden = true;
  }

  GetNavigationTitle() {
    if (this.activatedRoute.firstChild) {
      return this.activatedRoute.firstChild.snapshot.data['title'];
    }
  }
}
