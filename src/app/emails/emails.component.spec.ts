import { ComponentFixture, TestBed } from "@angular/core/testing";
import { provideHttpClient } from "@angular/common/http";
import { provideAnimations } from "@angular/platform-browser/animations";
import { provideRouter } from "@angular/router";
import { NgbModule } from "@ng-bootstrap/ng-bootstrap";
import { FontAwesomeModule } from "@fortawesome/angular-fontawesome";
import { MatDialogModule } from "@angular/material/dialog";
import { ToastrModule } from "ngx-toastr";

import { EmailsComponent } from "./emails.component";

describe("EmailsComponent", () => {
    let component: EmailsComponent;
    let fixture: ComponentFixture<EmailsComponent>;

    beforeEach(async () => {
        await TestBed.configureTestingModule({
            imports: [EmailsComponent],
            providers: [
                provideHttpClient(),
                provideAnimations(),
                provideRouter([]),
            ],
        }).compileComponents();

        fixture = TestBed.createComponent(EmailsComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
