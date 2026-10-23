import { ComponentFixture, TestBed } from "@angular/core/testing";
import { provideHttpClient } from "@angular/common/http";
import { provideAnimations } from "@angular/platform-browser/animations";
import { provideRouter } from "@angular/router";
import { NgbModule } from "@ng-bootstrap/ng-bootstrap";
import { FontAwesomeModule } from "@fortawesome/angular-fontawesome";
import { MatDialogModule } from "@angular/material/dialog";
import { ToastrModule } from "ngx-toastr";

import { EmailDetailComponent } from "./email-detail.component";

describe("EmailDetailComponent", () => {
    let component: EmailDetailComponent;
    let fixture: ComponentFixture<EmailDetailComponent>;

    beforeEach(async () => {
        await TestBed.configureTestingModule({
            imports: [EmailDetailComponent],
            providers: [
                provideHttpClient(),
                provideAnimations(),
                provideRouter([]),
            ],
        }).compileComponents();

        fixture = TestBed.createComponent(EmailDetailComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
