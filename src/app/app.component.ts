import { Component, OnInit } from "@angular/core";
import { NgbModal, NgbModalConfig } from "@ng-bootstrap/ng-bootstrap";
import { debounceTime, distinctUntilChanged, switchMap } from "rxjs/operators";

import {
    ActivatedRoute,
    RouterLink,
    RouterLinkActive,
    RouterOutlet,
} from "@angular/router";
import { WordPressService } from "./core/services/request.service";
import { LoginModalComponent } from "./login-modal/login-modal.component";
import { Observable } from "rxjs";
import { StateService } from "./core/services/state.service";
import { appRoutes } from "./route.config";
import { faAngleRight } from "@fortawesome/free-solid-svg-icons";
import { environment } from "../environments/environment";
import { version } from "../environments/version";
import { CommonModule } from "@angular/common";
import { FormsModule } from "@angular/forms";
import { NgbModule } from "@ng-bootstrap/ng-bootstrap";
import { FontAwesomeModule } from "@fortawesome/angular-fontawesome";

@Component({
    selector: "teamportal-root",
    standalone: true,
    imports: [
        CommonModule,
        FormsModule,
        RouterLink,
        RouterLinkActive,
        RouterOutlet,
        NgbModule,
        FontAwesomeModule,
        LoginModalComponent,
    ],
    templateUrl: "./app.component.html",
    styleUrls: ["./app.component.css"],
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
    isProd = false;
    gitVersion = version; // this will hold both branch and hash info
    private isAuthenticatedSubscriptionSet = false;
    private loginModalOpen = false;

    constructor(
        private activatedRoute: ActivatedRoute,
        private modalService: NgbModal,
        private stateService: StateService,
        private wordPressService: WordPressService,
        config: NgbModalConfig,
    ) {
        config.backdrop = "static";
        config.keyboard = false;

        this.appRoutes = appRoutes.filter((appRoute) => appRoute.path !== "");

        if (environment.production) {
            this.isProd = true;
        }
    }

    onLinkClick() {
        this.isNavbarHidden = true;
    }

    toggleNavbar() {
        this.isNavbarHidden = !this.isNavbarHidden;
    }

    GetNavigationTitle() {
        if (this.activatedRoute.firstChild) {
            return this.activatedRoute.firstChild.snapshot.data["title"];
        }
    }

    ngOnInit() {
        this.stateService.isAuthenticated.subscribe((value) => {
            if (value) {
                return;
            }

            if (this.loginModalOpen) {
                return;
            }

            this.loginModalOpen = true;
            const modalRef = this.modalService.open(LoginModalComponent, {
                centered: true,
            });
            modalRef.result.then(
                () => {
                    this.loginModalOpen = false;
                },
                () => {
                    this.loginModalOpen = false;
                },
            );
        });

        this.loadMenuItems();

        if (!this.isAuthenticatedSubscriptionSet) {
            this.isAuthenticatedSubscriptionSet = true;
            this.stateService.isAuthenticated.subscribe((isAuthenticated) => {
                if (isAuthenticated) {
                    this.loadMenuItems();
                }
            });
        }
    }

    loadMenuItems() {
        this.wordPressService.GetGroupsOfUser().subscribe((response) => {
            this.ShowMenuItems(response);
            this.isWebcie =
                response.findIndex((group) => group === "webcie") !== -1;
            this.stateService.isWebcie = this.isWebcie;
        });
    }

    ShowMenuItems(groups: string[]) {
        groups.forEach((subscription: string) => {
            this.appRoutes.forEach((appRoute: any) => {
                if (appRoute.data.groups) {
                    appRoute.data.groups.forEach((group: string) => {
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
            this.stateService.impersonationId = "";
        }
    }

    searchUsers = (text: Observable<string>) =>
        text.pipe(
            debounceTime(300),
            distinctUntilChanged(),
            switchMap(
                (term) =>
                    this.wordPressService.GetUsers(term) as Observable<
                        readonly any[]
                    >,
            ),
        );

    formatter = (gebruiker: { id: string; naam: string }) => {
        this.stateService.impersonationId = gebruiker.id;
        return gebruiker.naam;
    };
}
