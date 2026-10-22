import { Component, OnInit } from "@angular/core";
import { CommonModule } from "@angular/common";
import { NgbActiveModal, NgbModule } from "@ng-bootstrap/ng-bootstrap";
import { TeamtakencoService } from "../../core/services/teamtakenco.service";
import { SpinnerComponent } from "../../shared/spinner/spinner.component";

@Component({
    selector: "teamportal-selecteer-tellers",
    standalone: true,
    imports: [CommonModule, NgbModule, SpinnerComponent],
    templateUrl: "./selecteer-tellers.component.html",
    styleUrls: ["./selecteer-tellers.component.scss"],
})
export class SelecteerTellersComponent implements OnInit {
    static wedstrijd: any = {};
    static tijd: string = "";
    static tellerIndex: number = 0;

    tellersOptiesLoading!: boolean;
    spelendeTeams: any[] = [];
    overigeTeams: any[] = [];
    errorMessage!: string;
    wedstrijd: any = {};
    teams: string = "";
    tijd: string = "";
    tellerIndex: number = 0;

    constructor(
        public modal: NgbActiveModal,
        private teamtakencoService: TeamtakencoService,
    ) {}

    ngOnInit() {
        this.wedstrijd = SelecteerTellersComponent.wedstrijd;
        this.tijd = SelecteerTellersComponent.tijd;
        this.tellerIndex = SelecteerTellersComponent.tellerIndex;
        this.teams = this.wedstrijd.teams;

        this.getTelTeams(this.wedstrijd.matchId);
    }

    getTelTeams(matchId: any) {
        this.tellersOptiesLoading = true;

        this.teamtakencoService.GetTelTeams(matchId).subscribe(
            (zaalwachtopties: any) => {
                this.spelendeTeams = zaalwachtopties.spelendeTeams;
                this.overigeTeams = zaalwachtopties.overigeTeams;
                this.tellersOptiesLoading = false;
            },
            (error: any) => {
                if (error.status === 500) {
                    this.errorMessage = error.error.message;
                    this.tellersOptiesLoading = false;
                }
            },
        );
    }

    UpdateTeller(teller: any) {
        const $this = this;
        this.teamtakencoService
            .UpdateTellers(this.wedstrijd.matchId, teller.id, this.tellerIndex)
            .subscribe(() =>
                this.modal.close({ teller, tellerIndex: $this.tellerIndex }),
            );
    }
}
