import { Component } from '@angular/core';
import { FormBuilder, Validators } from '@angular/forms';
import { NgbModal, NgbModalConfig } from '@ng-bootstrap/ng-bootstrap';
import { JoomlaService } from '../core/services/request.service';

@Component({
  selector: 'teamportal-login-modal',
  templateUrl: './login-modal.component.html',
  styleUrls: ['./login-modal.component.scss'],
  providers: [NgbModalConfig, NgbModal]
})
export class LoginModalComponent {
  loginForm: any;
  errorMessage: string;

  constructor(private fb: FormBuilder, private joomalService: JoomlaService) {
    this.loginForm = this.fb.group({
      username: ['', Validators.required],
      password: ['', Validators.required]
    });
  }

  login() {
    this.errorMessage = null;
    const username = this.loginForm.get('username').value;
    const password = this.loginForm.get('password').value;
    this.joomalService.Login(username, password).subscribe(
      () => window.location.reload(),
      error => {
        this.errorMessage = error.error.message;
      }
    );
  }
}
