import { FormBuilder, Validators } from '@angular/forms';
import { NgbModal, NgbModalConfig } from '@ng-bootstrap/ng-bootstrap';

import { Component } from '@angular/core';
import { JoomlaService } from '../core/services/request.service';
import { Router } from '@angular/router';
import { StateService } from '../core/services/state.service';

@Component({
  selector: 'teamportal-login-modal',
  templateUrl: './login-modal.component.html',
  styleUrls: ['./login-modal.component.scss'],
  providers: [NgbModalConfig, NgbModal],
})
export class LoginModalComponent {
  loginForm: any;
  errorMessage: string;

  constructor(
    private fb: FormBuilder,
    private joomalService: JoomlaService,
    private modelService: NgbModal,
    private stateService: StateService,
    private router: Router
  ) {
    this.loginForm = this.fb.group({
      username: ['', Validators.required],
      password: ['', Validators.required],
    });
  }

  login() {
    this.errorMessage = null;
    const username = this.loginForm.get('username').value;
    const password = this.loginForm.get('password').value;
    this.joomalService.Login(username, password).subscribe({
      next: () => {
        this.stateService.setIsAuthenticated(true);
        this.router.navigate(['/']);
        this.modelService.dismissAll();
      },
      error: (error) => {
        this.errorMessage = error.error.message;
      },
    });
  }
}
