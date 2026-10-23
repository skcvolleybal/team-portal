import { Component, OnInit } from "@angular/core";
import { CommonModule } from "@angular/common";
import { AanwezigheidService } from "../../core/services/aanwezigheid.service";
import { WordPressService } from "../../core/services/request.service";
import { SpinnerComponent } from "../../shared/spinner/spinner.component";
import { KeuzeComponent } from "../../shared/keuze/keuze.component";
import { SpelersLijstComponent } from "../spelers-lijst/spelers-lijst.component";
import { InvalTeamsComponent } from "../inval-teams/inval-teams.component";
import { WedstrijdComponent } from "../../shared/wedstrijd/wedstrijd.component";

@Component({
    selector: "teamportal-wedstrijd-overzicht",
    standalone: true,
    imports: [
        CommonModule,
        SpinnerComponent,
        KeuzeComponent,
        SpelersLijstComponent,
        InvalTeamsComponent,
        WedstrijdComponent,
    ],
    templateUrl: "./wedstrijd-overzicht.component.html",
    styleUrls: ["./wedstrijd-overzicht.component.scss"],
})
export class WedstrijdOverzichtComponent implements OnInit {
    wedstrijden: any[] = [];
    loading!: boolean;
    errorMessage!: string;
    user!: any;
    wedstrijdenEmpty: boolean = false;

    SortTeam = (speler1: any, speler2: any) =>
        speler1.naam > speler2.naam ? 1 : -1;

    constructor(
        private aanwezigheidService: AanwezigheidService,
        private wordPressService: WordPressService,
    ) {}

    ngOnInit() {
        this.loading = true;
        this.wordPressService.GetWedstrijdOverzicht().subscribe(
            (wedstrijden) => {
                this.wedstrijden = wedstrijden;
                this.loading = false;
                if (this.wedstrijden.length == 0) {
                    this.wedstrijdenEmpty = true;
                }
            },
            (error) => {
                if (error.status === 500) {
                    this.errorMessage = error.error.message;
                    this.loading = false;
                }
            },
        );

        this.wordPressService.GetCurrentUser().subscribe((data) => {
            this.user = data;
        });
    }

    UpdateAanwezigheid(
        currentWedstrijd: any,
        isAanwezig: boolean | null,
        speler: any,
    ) {
        const matchId = currentWedstrijd.matchId;
        const rol =
            speler.rol ??
            (currentWedstrijd.isEigenWedstrijd ? "speler" : "coach");
        this.aanwezigheidService.UpdateAanwezigheid(
            currentWedstrijd.matchId,
            isAanwezig as boolean,
            speler.id,
            rol,
        );

        this.wedstrijden.forEach((wedstrijd) => {
            if (wedstrijd.matchId === matchId) {
                wedstrijd.afwezigen = wedstrijd.afwezigen.filter(
                    (afwezige: any) => afwezige.id !== speler.id,
                );
                wedstrijd.aanwezigen = wedstrijd.aanwezigen.filter(
                    (aanwezige: any) => aanwezige.id !== speler.id,
                );
                wedstrijd.onbekend = wedstrijd.onbekend.filter(
                    (onbekende: any) => onbekende.id !== speler.id,
                );

                if (rol === "invaller" && isAanwezig === null) {
                    return;
                }

                const newSpeler = {
                    id: speler.id,
                    naam: speler.naam,
                    rol,
                };

                switch (isAanwezig) {
                    case true:
                        wedstrijd.aanwezigen.push(newSpeler);
                        wedstrijd.aanwezigen.sort(this.SortTeam);
                        break;
                    case false:
                        wedstrijd.afwezigen.push(newSpeler);
                        wedstrijd.afwezigen.sort(this.SortTeam);
                        break;
                    case null:
                        wedstrijd.onbekend.push(newSpeler);
                        wedstrijd.onbekend.sort(this.SortTeam);
                        break;
                }

                return;
            }
        });
    }
}
