import { Component, EventEmitter, Input, Output } from "@angular/core";
import { CommonModule } from "@angular/common";
import { FontAwesomeModule } from "@fortawesome/angular-fontawesome";
import { SpinnerComponent } from "../../../shared/spinner/spinner.component";
import {
    faMinusSquare,
    faPlusSquare,
    faTimesCircle,
} from "@fortawesome/free-solid-svg-icons";

@Component({
    selector: "teamportal-spelers-lijst",
    standalone: true,
    imports: [CommonModule, FontAwesomeModule, SpinnerComponent],
    templateUrl: "./spelers-lijst.component.html",
    styleUrls: ["./spelers-lijst.component.scss"],
})
export class SpelersLijstComponent {
    @Input() spelers: any;
    @Input() title: string;
    @Input() class: string;

    @Output() deleteSpeler = new EventEmitter<any>();

    inklappen = faMinusSquare;
    uitklappen = faPlusSquare;
    verwijderIcon = faTimesCircle;
    isCollapsed = true;

    get numberOfPlayers() {
        return this.spelers.filter((speler) => speler.rol !== "coach").length;
    }

    constructor() {}

    DeleteSpeler(speler: any) {
        this.deleteSpeler.emit(speler);
    }
}
