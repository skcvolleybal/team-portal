import { Component, OnInit } from "@angular/core";
import { CommonModule } from "@angular/common";
import { FontAwesomeModule } from "@fortawesome/angular-fontawesome";
import { StatisticsService } from "../core/services/statistics.service";
import { SpinnerComponent } from "../shared/spinner/spinner.component";

@Component({
    selector: "tp-statistieken",
    standalone: true,
    imports: [CommonModule, FontAwesomeModule, SpinnerComponent],
    templateUrl: "./statistieken.component.html",
    styleUrls: ["./statistieken.component.scss"],
})
export class StatistiekenComponent implements OnInit {
    skcRankingData!: any;
    loading!: boolean;
    errorMessage!: string;

    constructor(private statisticsService: StatisticsService) {}

    async ngOnInit() {
        this.getSkcRankingData();
    }

    async getSkcRankingData() {
        this.loading = true;
        this.statisticsService.getSkcRanking().subscribe(
            (data) => {
                this.loading = false;
                this.skcRankingData = data;
                return data;
            },
            (error) => {
                console.error("error getting stats data", error);
                this.errorMessage = error.error.message;
                this.loading = false;
            },
        );
    }
}
