import { Component, EventEmitter, Input, OnInit, Output } from "@angular/core";
import { CommonModule } from "@angular/common";
import { FontAwesomeModule } from "@fortawesome/angular-fontawesome";
import { NgbModule } from "@ng-bootstrap/ng-bootstrap";
import {
    faInfoCircle,
    faMinusSquare,
    faPlus,
    faPlusSquare,
    faTimesCircle,
} from "@fortawesome/free-solid-svg-icons";

@Component({
    selector: "teamportal-inval-teams",
    standalone: true,
    imports: [CommonModule, FontAwesomeModule, NgbModule],
    templateUrl: "./inval-teams.component.html",
    styleUrls: ["./inval-teams.component.scss"],
})
export class InvalTeamsComponent implements OnInit {
    @Input()
    teams!: any;
    @Output()
    addAanwezigheid = new EventEmitter();

    uitklappen = faPlusSquare;
    inklappen = faMinusSquare;
    spelerToevoegen = faPlus;
    info = faInfoCircle;

    isCollapsed = true;
    isTeamCollapsed!: boolean[];
    class: string;
    title: string;
    numberOfPlayers: number;
    spelers: any[];
    verwijderIcon = faTimesCircle;
    constructor() {}

    AddAanwezigheid(speler: any) {
        speler.rol = "invaller";
        this.addAanwezigheid.emit(speler);
    }

    DeleteSpeler(speler: any) {
        // Stub
    }

    ngOnInit() {
        if (this.teams) {
            this.isTeamCollapsed = new Array(this.teams.length).fill(true);
        }
    }
}
