import { Component, Input, OnInit } from "@angular/core";
import { CommonModule } from "@angular/common";
import { WedstrijdComponent } from "../wedstrijd/wedstrijd.component";
import { ScheidsrechterComponent } from "../scheidsrechter/scheidsrechter.component";
import { TellersComponent } from "../tellers/tellers.component";

@Component({
    selector: "teamportal-wedstrijden-card",
    standalone: true,
    imports: [
        CommonModule,
        WedstrijdComponent,
        ScheidsrechterComponent,
        TellersComponent,
    ],
    templateUrl: "./wedstrijden-card.component.html",
    styleUrls: ["./wedstrijden-card.component.scss"],
})
export class WedstrijdenCardComponent implements OnInit {
    @Input()
    wedstrijden!: any[];

    constructor() {}

    ngOnInit() {}
}
