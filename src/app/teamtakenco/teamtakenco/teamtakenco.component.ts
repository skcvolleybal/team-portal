import { Component, OnInit } from "@angular/core";
import {
    faCalendarCheck,
    faPeopleCarry,
    faTrashAlt,
    faUserTie as scheidsIcon,
} from "@fortawesome/free-solid-svg-icons";
import { CommonModule } from "@angular/common";
import { FontAwesomeModule } from "@fortawesome/angular-fontawesome";
import { NgbModal, NgbModule } from "@ng-bootstrap/ng-bootstrap";
import { TeamtakencoService } from "../../core/services/teamtakenco.service";
import { SelecteerScheidsrechterComponent } from "../selecteer-scheidsrechter/selecteer-scheidsrechter.component";
import { SelecteerTellersComponent } from "../selecteer-tellers/selecteer-tellers.component";
import { SelecteerZaalwachtComponent } from "../selecteer-zaalwacht/selecteer-zaalwacht.component";
import { ExportascsvComponent } from "../exportascsv/exportascsv.component";
import { Speeldag } from "../../models/Speeldag";
import { SpinnerComponent } from "../../shared/spinner/spinner.component";
import { Wedstrijd } from "../../models/Wedstrijd";

type Zaalwachttype = "eerste" | "tweede";

@Component({
    selector: "teamportal-teamtakenco",
    standalone: true,
    imports: [
        CommonModule,
        FontAwesomeModule,
        NgbModule,
        SelecteerScheidsrechterComponent,
        SelecteerTellersComponent,
        SelecteerZaalwachtComponent,
        ExportascsvComponent,
        SpinnerComponent,
    ],
    templateUrl: "./teamtakenco.component.html",
    styleUrls: ["./teamtakenco.component.scss"],
})
export class TeamtakencoComponent implements OnInit {
    icons = {
        scheidsrechter: scheidsIcon,
        tellers: faCalendarCheck,
        zaalwacht: faPeopleCarry,
        verwijderen: faTrashAlt,
    };

    scheidsrechters!: any[];
    speeldagen: Speeldag[] = [];
    taken = ["scheidsrechter", "tellers"];
    zaalwachttypes: Zaalwachttype[] = ["eerste", "tweede"];
    overzichtLoading!: boolean;
    errorMessage: any;
    speeldagenEmpty: boolean = false;
    constructor(
        private teamtakencoService: TeamtakencoService,
        private modalService: NgbModal,
    ) {}

    getTeamtakencoOverzicht() {
        this.overzichtLoading = true;
        this.teamtakencoService.GetTeamtakencoOverzicht().subscribe(
            (speeldagen) => {
                this.speeldagen = speeldagen;
                this.overzichtLoading = false;
                if (this.speeldagen.length == 0) {
                    this.speeldagenEmpty = true;
                }
            },
            (error) => {
                if (error.status === 500) {
                    this.errorMessage = error.error.message;
                }
                this.overzichtLoading = false;
            },
        );
    }

    ngOnInit() {
        this.getTeamtakencoOverzicht();
    }

    getZaalwacht(speeldag: Speeldag, type: Zaalwachttype): string | null {
        return type === "eerste"
            ? (speeldag.eersteZaalwacht ?? null)
            : (speeldag.tweedeZaalwacht ?? null);
    }

    getZaalwachtShortNotation(speeldag: Speeldag, type: Zaalwachttype): string {
        return type === "eerste"
            ? (speeldag.eersteZaalwachtShortNotation ?? "")
            : (speeldag.tweedeZaalwachtShortNotation ?? "");
    }

    SelecteerZaalwacht(
        datum: string,
        date: string,
        zaalwachttype: Zaalwachttype,
    ) {
        const component = SelecteerZaalwachtComponent;
        component.date = date;
        component.datum = datum;
        component.zaalwachttype = zaalwachttype;
        this.modalService
            .open(component)
            .result.then((team) => {
                this.SetZaalwacht(date, team, zaalwachttype);
            })
            .catch(() => {});
    }

    getShortNotation(name: string): string {
        if (!name) {
            return "";
        }
        const gender = name.charAt(0);
        const teamnumber = name.substring(6);
        return `${gender}${teamnumber}`;
    }

    SetZaalwacht(date: string, team: string, zaalwachttype: Zaalwachttype) {
        const speeldag = this.speeldagen.find((dag) => dag.date === date);
        if (!speeldag) return;
        if (zaalwachttype === "eerste") {
            speeldag.eersteZaalwacht = team;
            speeldag.eersteZaalwachtShortNotation = this.getShortNotation(team);
        } else {
            speeldag.tweedeZaalwacht = team;
            speeldag.tweedeZaalwachtShortNotation = this.getShortNotation(team);
        }
    }

    SelecteerScheidsrechter(geselecteerdeWedstrijd: Wedstrijd, tijd: string) {
        const component = SelecteerScheidsrechterComponent;
        component.wedstrijd = geselecteerdeWedstrijd;
        component.tijd = tijd;
        const $this = this;
        this.modalService
            .open(component)
            .result.then((result) =>
                $this.SetScheidsrechter(geselecteerdeWedstrijd.matchId, result),
            )
            .catch(() => {});
    }

    SelecteerTeller(
        geselecteerdeWedstrijd: Wedstrijd,
        tijd: string,
        tellerIndex: number,
    ) {
        const component = SelecteerTellersComponent;
        component.tellerIndex = tellerIndex;
        component.wedstrijd = geselecteerdeWedstrijd;
        component.tijd = tijd;
        this.modalService
            .open(component)
            .result.then((result) => {
                this.SetTeller(
                    geselecteerdeWedstrijd.matchId,
                    result.teller.naam,
                    result.tellerIndex,
                );
            })
            .catch(() => {});
    }

    SetScheidsrechter(matchId: any, scheidsrechter: any) {
        this.speeldagen.forEach((speeldag) => {
            speeldag.speeltijden.forEach((speeltijd: any) => {
                speeltijd.wedstrijden.forEach((wedstrijd: any) => {
                    if (wedstrijd.matchId === matchId) {
                        wedstrijd.scheidsrechter = scheidsrechter;
                        return;
                    }
                });
            });
        });
    }

    SetTeller(matchId: any, teller: any, tellerIndex: any) {
        this.speeldagen.forEach((speeldag) => {
            speeldag.speeltijden.forEach((speeltijd: any) => {
                speeltijd.wedstrijden.forEach((wedstrijd: any) => {
                    if (wedstrijd.matchId === matchId) {
                        wedstrijd.tellers[tellerIndex] = teller;
                        return;
                    }
                });
            });
        });
    }

    DeleteScheidsrechter(matchId: string) {
        this.teamtakencoService
            .UpdateScheidsrechter(matchId, null as any)
            .subscribe();
        this.SetScheidsrechter(matchId, null as any);
    }

    DeleteTeller(matchId: string, tellerIndex: number) {
        this.teamtakencoService
            .UpdateTellers(matchId, null as any, tellerIndex)
            .subscribe();
        this.SetTeller(matchId, null as any, tellerIndex);
    }

    DeleteZaalwacht(date: string, zaalwachttype: Zaalwachttype) {
        this.teamtakencoService
            .UpdateZaalwacht(date, null as any, zaalwachttype)
            .subscribe();
        this.SetZaalwacht(date, null as any, zaalwachttype);
    }
}
