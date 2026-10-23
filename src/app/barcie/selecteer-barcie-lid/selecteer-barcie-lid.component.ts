import { Component, OnInit } from "@angular/core";
import { CommonModule } from "@angular/common";
import { NgbActiveModal, NgbModule } from "@ng-bootstrap/ng-bootstrap";
import { AanwezigheidService } from "../../core/services/aanwezigheid.service";
import { BarcieService } from "../../core/services/barcie.service";
import { SpinnerComponent } from "../../shared/spinner/spinner.component";

@Component({
    selector: "teamportal-selecteer-barcie-lid",
    standalone: true,
    imports: [CommonModule, NgbModule, SpinnerComponent],
    templateUrl: "./selecteer-barcie-lid.component.html",
    styleUrls: ["./selecteer-barcie-lid.component.scss"],
})
export class SelecteerBarcielidComponent implements OnInit {
    static date: string = "";
    static shift: number = 0;
    static datum: string = "";

    date!: string;
    datum!: string;
    shift!: number;
    beschikbaarheden: any = {};
    isLoading!: boolean;
    errorMessage!: string;

    constructor(
        public modal: NgbActiveModal,
        private barcieService: BarcieService,
        private aanwezigheidService: AanwezigheidService,
    ) {}

    ngOnInit() {
        this.date = SelecteerBarcielidComponent.date;
        this.datum = SelecteerBarcielidComponent.datum;
        this.shift = SelecteerBarcielidComponent.shift;
        this.GetBarLeden();
    }

    GetBarLeden() {
        this.isLoading = true;
        this.barcieService.GetBarcieBeschikbaarheden(this.date).subscribe(
            (response: any) => {
                this.isLoading = false;
                this.beschikbaarheden = response;
            },
            (response: any) => {
                this.isLoading = false;
                this.errorMessage = response.error.message;
            },
        );
    }

    AddBarcieAanwezigheid(barcielid: any) {
        this.aanwezigheidService
            .AddBarcieAanwezigheid(this.date, this.shift, barcielid.id)
            .subscribe(
                () => {
                    this.modal.close(barcielid);
                },
                (response: any) => {
                    this.errorMessage = response.error.message;
                },
            );
    }
}
