import { Component, EventEmitter, Input, Output } from "@angular/core";
import { CommonModule } from "@angular/common";
import { FormsModule, ReactiveFormsModule } from "@angular/forms";
import { NgbModule, NgbModal } from "@ng-bootstrap/ng-bootstrap";
import { FontAwesomeModule } from "@fortawesome/angular-fontawesome";
import { UntypedFormBuilder, Validators } from "@angular/forms";
import { Router } from "@angular/router";
import { WordPressService } from "../core/services/request.service";
import { StateService } from "../core/services/state.service";

@Component({
    selector: "teamportal-login-modal",
    standalone: true,
    imports: [CommonModule, FormsModule, ReactiveFormsModule, NgbModule],
    templateUrl: "./login-modal.component.html",
    styleUrls: ["./login-modal.component.scss"],
    providers: [],
})
export class LoginModalComponent {
    loginForm: any;
    errorMessage: string = "";

    constructor(
        private fb: UntypedFormBuilder,
        private wordPressService: WordPressService,
        private modelService: NgbModal,
        private stateService: StateService,
        private router: Router,
    ) {
        this.loginForm = this.fb.group({
            username: ["", Validators.required],
            password: ["", Validators.required],
        });
    }

    login() {
        this.errorMessage = "";
        const username = this.loginForm.get("username").value;
        const password = this.loginForm.get("password").value;
        this.wordPressService.Login(username, password).subscribe({
            next: () => {
                this.stateService.setIsAuthenticated(true);
                this.router.navigate(["/"]);
                this.modelService.dismissAll();
            },
            error: (error) => {
                this.errorMessage = error.error.message;
            },
        });
    }
}
