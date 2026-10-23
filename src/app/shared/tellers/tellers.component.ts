import { Component, Input, OnInit } from "@angular/core";
import { CommonModule } from "@angular/common";
import { FontAwesomeModule } from "@fortawesome/angular-fontawesome";
import { faCalendarCheck } from "@fortawesome/free-solid-svg-icons";

@Component({
    selector: "teamportal-tellers",
    standalone: true,
    imports: [CommonModule, FontAwesomeModule],
    templateUrl: "./tellers.component.html",
    styleUrls: ["./tellers.component.scss"],
})
export class TellersComponent implements OnInit {
    @Input()
    isTellers!: any;
    @Input()
    tellers!: any;

    teamIcon = faCalendarCheck;

    constructor() {}

    ngOnInit() {}
}
