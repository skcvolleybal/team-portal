import { Component, OnInit } from "@angular/core";
import { CommonModule } from "@angular/common";
import { NgbActiveModal, NgbModule } from "@ng-bootstrap/ng-bootstrap";
import { TeamtakencoService } from "../../core/services/teamtakenco.service";
import { SpinnerComponent } from "../../shared/spinner/spinner.component";

@Component({
    selector: "teamportal-selecteer-zaalwacht",
    standalone: true,
    imports: [CommonModule, NgbModule, SpinnerComponent],
    templateUrl: "./selecteer-zaalwacht.component.html",
    styleUrls: ["./selecteer-zaalwacht.component.scss"],
})
export class SelecteerZaalwachtComponent implements OnInit {
    constructor(
        public modal: NgbActiveModal,
        private teamtakencoService: TeamtakencoService,
    ) {}

    static date: string = "";
    static datum: string = "";
    static zaalwachttype: string = "";

    spelendeTeams: any[] = [];
    overigeTeams: any[] = [];

    datum: string = "";
    date: string = "";
    zaalwachttype: string = "";
    zaalwachtoptiesLoading!: boolean;
    errorMessage!: string;

    ngOnInit() {
        this.datum = SelecteerZaalwachtComponent.datum;
        this.date = SelecteerZaalwachtComponent.date;
        this.zaalwachttype = SelecteerZaalwachtComponent.zaalwachttype;
        this.getZaalwachtOpties(this.date);
    }

    getZaalwachtOpties(date: any) {
        this.zaalwachtoptiesLoading = true;

        this.teamtakencoService.GetZaalwachtOpties(date).subscribe(
            (zaalwachtopties: any) => {
                this.spelendeTeams = zaalwachtopties.spelendeTeams;
                this.overigeTeams = zaalwachtopties.overigeTeams;
                this.zaalwachtoptiesLoading = false;
            },
            (error: any) => {
                if (error.status === 500) {
                    this.errorMessage = error.error.message;
                    this.zaalwachtoptiesLoading = false;
                }
            },
        );
    }

    UpdateZaalwacht(team: string | null) {
        this.teamtakencoService
            .UpdateZaalwacht(this.date, team, this.zaalwachttype)
            .subscribe(() => this.modal.close(team));
    }
}
