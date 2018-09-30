import { HttpClient } from '@angular/common/http';
import { Component, OnInit } from '@angular/core';
import { NgbModal, NgbModalConfig } from '@ng-bootstrap/ng-bootstrap';

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
        'http://localhost/php/interface.php?action=Login',
        {
          username: this.username,
          password: this.password
        },
        {
          withCredentials: true
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
