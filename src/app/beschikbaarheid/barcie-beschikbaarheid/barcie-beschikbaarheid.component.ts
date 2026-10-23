import { Component, OnInit } from "@angular/core";
import { CommonModule } from "@angular/common";
import { BeschikbaarheidService } from "../../core/services/beschikbaarheid.service";
import { SpinnerComponent } from "../../shared/spinner/spinner.component";
import { WedstrijdenCardComponent } from "../../shared/wedstrijden-card/wedstrijden-card.component";
import { KeuzeComponent } from "../../shared/keuze/keuze.component";

@Component({
    selector: "teamportal-barcie-beschikbaarheid",
    standalone: true,
    imports: [
        CommonModule,
        SpinnerComponent,
        WedstrijdenCardComponent,
        KeuzeComponent,
    ],
    templateUrl: "./barcie-beschikbaarheid.component.html",
    styleUrls: ["./barcie-beschikbaarheid.component.scss"],
})
export class BarcieBeschikbaarheidComponent implements OnInit {
    loading!: boolean;
    speeldagen!: any[];
    errorMessage!: string;
    speeldagenEmpty!: boolean;

    constructor(private beschikbaarheidService: BeschikbaarheidService) {}

    ngOnInit() {
        this.getBarcieBeschikbaarheid();
    }

    UpdateBarcieBeschikbaarheid(beschikbaarheid: any, date: string) {
        this.beschikbaarheidService
            .UpdateBarcieBeschikbaarheid(date, beschikbaarheid)
            .subscribe();
    }

    getBarcieBeschikbaarheid() {
        this.loading = true;
        this.beschikbaarheidService.GetBarcieBeschikbaarheid().subscribe(
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
