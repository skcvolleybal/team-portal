import { Component, OnInit } from "@angular/core";
import { CommonModule } from "@angular/common";
import {
    FormsModule,
    ReactiveFormsModule,
    UntypedFormControl,
    UntypedFormGroup,
    Validators,
} from "@angular/forms";
import { NgbDate, NgbModal, NgbModule } from "@ng-bootstrap/ng-bootstrap";
import {
    faUser,
    faTrash,
    faCalendarAlt,
    faTrashAlt,
    faHeart as heartSolid,
} from "@fortawesome/free-solid-svg-icons";
import { FontAwesomeModule } from "@fortawesome/angular-fontawesome";

import { AanwezigheidService } from "../../core/services/aanwezigheid.service";
import { BarcieService } from "../../core/services/barcie.service";
import { SelecteerBarcielidComponent } from "../selecteer-barcie-lid/selecteer-barcie-lid.component";
import { SpinnerComponent } from "../../shared/spinner/spinner.component";
import { faAmbulance as BHV } from "@fortawesome/free-solid-svg-icons";
import { faUser as noBHV } from "@fortawesome/free-solid-svg-icons";

@Component({
    selector: "teamportal-barcie-indeling",
    standalone: true,
    imports: [
        CommonModule,
        FormsModule,
        ReactiveFormsModule,
        NgbModule,
        FontAwesomeModule,
        SelecteerBarcielidComponent,
        SpinnerComponent,
    ],
    templateUrl: "./barcie-indeling.component.html",
    styleUrls: ["./barcie-indeling.component.scss"],
})
export class BarcieIndelingComponent implements OnInit {
    calendar = faCalendarAlt;
    user = faUser;
    geenBhv = noBHV;
    bhv = BHV;
    delete = faTrashAlt;
    newDate!: UntypedFormGroup;

    barciedagen: any[] = [];
    isLoading!: boolean;
    errorMessage!: string;
    bardiedagenEmpty: boolean = false;

    constructor(
        private barcieService: BarcieService,
        private aanwezigheidService: AanwezigheidService,
        private modalService: NgbModal,
    ) {}

    ngOnInit() {
        this.newDate = new UntypedFormGroup({
            date: new UntypedFormControl(null, [Validators.required]),
        });
        this.GetBarcieRooster();
    }

    onDateSelection(date: any) {
        this.errorMessage = "";
        this.barcieService.AddBarcieDag(date).subscribe(
            () => {
                this.GetBarcieRooster();
            },
            (response) => {
                this.errorMessage = response.error.message;
            },
        );
    }

    DeleteBarcieDate(date: string) {
        this.barcieService.DeleteBarcieDag(date).subscribe(
            () => {
                this.GetBarcieRooster();
            },
            (response) => {
                this.errorMessage = response.error.message;
            },
        );
    }

    ToggleBhv(selectedBarcieDag: any, selectedBarcielid: any, shift: any) {
        this.barcieService.ToggleBhv(
            selectedBarcieDag.date,
            shift,
            selectedBarcielid.id,
        );

        this.barciedagen.forEach((barciedag) => {
            if (barciedag.date === selectedBarcieDag.date) {
                const barshift = barciedag.shifts.find(
                    (candidate: any) => candidate.shift === Number(shift),
                );
                if (!barshift) {
                    return;
                }
                barshift.barleden.forEach((barcielid: any) => {
                    if (selectedBarcielid.id === barcielid.id) {
                        barcielid.isBhv = !barcielid.isBhv;
                    }
                });
            }
        });
    }

    DeleteAanwezigheid(
        selectedBarcieDag: any,
        selectedBarcielid: any,
        shiftNumber: any,
    ) {
        this.aanwezigheidService
            .DeleteBarcieAanwezigheid(
                selectedBarcieDag.date,
                shiftNumber,
                selectedBarcielid.id,
            )
            .subscribe(() => {
                this.barciedagen.forEach((barciedag) => {
                    if (barciedag.date === selectedBarcieDag.date) {
                        barciedag.shifts.forEach((shift: any) => {
                            shift.barleden.forEach((barlid: any, i: number) => {
                                if (
                                    selectedBarcielid.id === barlid.id &&
                                    shift.shift === shiftNumber
                                ) {
                                    shift.barleden.splice(i, 1);
                                    return;
                                }
                            });
                        });
                    }
                });
            });
    }

    GetBarcieRooster() {
        this.isLoading = true;
        this.barcieService.GetBarcieRooster().subscribe(
            (response) => {
                this.isLoading = false;
                this.barciedagen = response;
                if (this.barciedagen.length == 0) {
                    this.bardiedagenEmpty = true;
                }
            },
            (response) => {
                this.isLoading = false;
                this.errorMessage = response.error.message;
            },
        );
    }

    AddShift(date: string) {
        this.barciedagen.forEach((barciedag) => {
            if (barciedag.date === date) {
                const lastShift =
                    barciedag.shifts[barciedag.shifts.length - 1].shift;
                barciedag.shifts.push({
                    barleden: [],
                    shift: lastShift + 1,
                });
                return;
            }
        });
    }

    getShiftRows(shifts: any[]): any[][] {
        const rows = [];
        for (let i = 0; i < shifts.length; i += 3) {
            rows.push(shifts.slice(i, i + 3));
        }
        return rows;
    }

    SelecteerBarcieLid(geselecteerdeBarcieDag: any, shift: number) {
        const component = SelecteerBarcielidComponent;
        component.date = geselecteerdeBarcieDag.date;
        component.datum = geselecteerdeBarcieDag.datum;
        component.shift = shift;
        this.modalService
            .open(component)
            .result.then((barcielid: { id: number; naam: string }) => {
                if (!barcielid) {
                    return;
                }
                this.GetBarcieRooster();
            })
            .catch(() => {});
    }
}
