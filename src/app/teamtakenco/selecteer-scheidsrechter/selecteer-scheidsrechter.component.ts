import { Component, OnInit } from "@angular/core";
import { CommonModule } from "@angular/common";
import { FontAwesomeModule } from "@fortawesome/angular-fontawesome";
import { NgbActiveModal, NgbModule } from "@ng-bootstrap/ng-bootstrap";
import { TeamtakencoService } from "./../../core/services/teamtakenco.service";
import { Team } from "../../models/Team";
import { SpinnerComponent } from "../../shared/spinner/spinner.component";
import { Wedstrijd } from "../../models/Wedstrijd";

@Component({
    selector: "teamportal-selecteer-scheidsrechter",
    standalone: true,
    imports: [CommonModule, FontAwesomeModule, NgbModule, SpinnerComponent],
    templateUrl: "./selecteer-scheidsrechter.component.html",
    styleUrls: ["./selecteer-scheidsrechter.component.scss"],
})
export class SelecteerScheidsrechterComponent implements OnInit {
    static wedstrijd: Wedstrijd = new Wedstrijd();
    static tijd: string = "";

    scheidsrechterOptiesLoading!: boolean;
    errorMessage!: string;
    scheidsrechters!: any[];

    scheidsrechtertypes = ["spelendeScheidsrechters", "overigeScheidsrechters"];
    keuzes = ["Ja", "Onbekend", "Nee"];

    wedstrijd: any;
    teams!: Team[];
    tijd!: string;

    constructor(
        private teamtakencoService: TeamtakencoService,
        public modal: NgbActiveModal,
    ) {}

    ngOnInit() {
        this.wedstrijd = SelecteerScheidsrechterComponent.wedstrijd;
        this.teams = this.wedstrijd.teams;
        this.tijd = SelecteerScheidsrechterComponent.tijd;
        this.getScheidsrechterOpties(this.wedstrijd.matchId);
    }

    getScheidsrechterOpties(matchId: string) {
        this.scheidsrechterOptiesLoading = true;

        this.teamtakencoService.GetScheidsrechtersForMatch(matchId).subscribe(
            (result) => {
                this.scheidsrechters = result;
                this.scheidsrechterOptiesLoading = false;
            },
            (error) => {
                if (error.status === 500) {
                    this.errorMessage = error.error.message;
                    this.scheidsrechterOptiesLoading = false;
                }
            },
        );
    }

    getScheidsrechtersByKeuze(index: number, keuze: string) {
        return this.scheidsrechters[index][keuze];
    }

    GetScheidsrechterText(scheidsrechter: any) {
        const result = [];
        if (scheidsrechter.niveau) {
            result.push(scheidsrechter.niveau);
        }
        result.push(`${scheidsrechter.naam} (${scheidsrechter.gefloten})`);
        if (scheidsrechter.eigenTijd) {
            result.push(scheidsrechter.eigenTijd);
        }
        return result.join(", ");
    }

    GetClass(scheidsrechter: any) {
        return {
            "btn-danger": scheidsrechter.isBeschikbaar === false,
            "btn-success": scheidsrechter.isBeschikbaar === true,
            "btn-warning": scheidsrechter.isBeschikbaar === null,
        };
    }

    GetRegularCasing(text: any) {
        return text
            .replace(/([A-Z])/g, " $1")
            .replace(/^./, (str: string) => str.toUpperCase());
    }

    UpdateScheidsrechter(scheidsrechter: any) {
        this.teamtakencoService
            .UpdateScheidsrechter(this.wedstrijd.matchId, scheidsrechter.id)
            .subscribe(() => {
                this.modal.close(scheidsrechter.naam);
            });
    }
}
