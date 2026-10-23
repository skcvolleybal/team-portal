import { ComponentFixture, TestBed } from "@angular/core/testing";
import { provideHttpClient } from "@angular/common/http";
import { provideAnimations } from "@angular/platform-browser/animations";
import { provideRouter } from "@angular/router";
import { NgbModule } from "@ng-bootstrap/ng-bootstrap";
import { FontAwesomeModule } from "@fortawesome/angular-fontawesome";
import { MatDialogModule } from "@angular/material/dialog";
import { provideToastService } from "ngx-yet-another-toast-library";

import { StatistiekenComponent } from "./statistieken.component";

describe("StatistiekenComponent", () => {
    let component: StatistiekenComponent;
    let fixture: ComponentFixture<StatistiekenComponent>;

    beforeEach(async () => {
        await TestBed.configureTestingModule({
            imports: [StatistiekenComponent],
            providers: [
                provideHttpClient(),
                provideAnimations(),
                provideRouter([]),
                provideToastService(),
            ],
        }).compileComponents();

        fixture = TestBed.createComponent(StatistiekenComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
