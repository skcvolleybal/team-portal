import { HttpClient } from '@angular/common/http';
import { Component, OnInit } from '@angular/core';
import { NgbModal, NgbModalConfig } from '@ng-bootstrap/ng-bootstrap';
// tslint:disable-next-line:no-implicit-dependencies
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-login-modal',
  templateUrl: './login-modal.component.html',
  styleUrls: ['./login-modal.component.scss'],
  providers: [NgbModalConfig, NgbModal]
})
export class LoginModalComponent implements OnInit {
  constructor(private httpClient: HttpClient) {}

  errorMessage: string;
  username: string;
  password: string;

  login() {
    this.errorMessage = null;

    this.httpClient
      .post<any>(
        environment.baseUrl,
        {
          username: this.username,
          password: this.password
        },
        {
          params: {
            action: 'Login'
          }
        }
      )
      .subscribe(
        () => window.location.reload(),
        error => {
          this.errorMessage = error.error;
        }
      );
  }

  ngOnInit() {}
}
