import { Component, OnInit } from "@angular/core";
import { CommonModule } from "@angular/common";
import { BeschikbaarheidService } from "../../core/services/beschikbaarheid.service";
import { SpinnerComponent } from "../../shared/spinner/spinner.component";
import { WedstrijdenCardComponent } from "../../shared/wedstrijden-card/wedstrijden-card.component";
import { KeuzeComponent } from "../../shared/keuze/keuze.component";

@Component({
    selector: "teamportal-fluit-beschikbaarheid",
    standalone: true,
    imports: [
        CommonModule,
        SpinnerComponent,
        WedstrijdenCardComponent,
        KeuzeComponent,
    ],
    templateUrl: "./tel-fluit-beschikbaarheid.component.html",
    styleUrls: ["./tel-fluit-beschikbaarheid.component.scss"],
})
export class TelFluitBeschikbaarheidComponent implements OnInit {
    loading!: boolean;
    speeldagen!: any[];
    errorMessage!: string;
    speeldagenEmpty: boolean = false;

    constructor(private beschikbaarheidService: BeschikbaarheidService) {}

    ngOnInit() {
        this.getFluitBeschikbaarheid();
    }

    UpdateFluitBeschikbaarheid(
        beschikbaarheid: any,
        datum: string,
        tijd: string,
    ) {
        this.beschikbaarheidService
            .UpdateFluitBeschikbaarheid(datum, tijd, beschikbaarheid)
            .subscribe();
    }

    getFluitBeschikbaarheid() {
        this.loading = true;
        this.beschikbaarheidService.GetFluitBeschikbaarheid().subscribe(
            (speeldagen: any[]) => {
                this.speeldagen = speeldagen;
                this.loading = false;
                if (this.speeldagen.length == 0) {
                    this.speeldagenEmpty = true;
                }
            },
            (error: any) => {
                if (error.status === 500) {
                    this.errorMessage = error.error.message;
                    this.loading = false;
                }
            },
        );
    }
}
