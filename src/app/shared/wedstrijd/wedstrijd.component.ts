import { Component, Input, OnInit } from "@angular/core";
import { CommonModule } from "@angular/common";

@Component({
    selector: "teamportal-wedstrijd",
    standalone: true,
    imports: [CommonModule],
    templateUrl: "./wedstrijd.component.html",
    styleUrls: ["./wedstrijd.component.scss"],
})
export class WedstrijdComponent implements OnInit {
    @Input()
    wedstrijd!: any;
    constructor() {}

    ngOnInit() {}
}
