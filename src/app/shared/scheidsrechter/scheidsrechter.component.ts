import { Component, Input, OnInit } from "@angular/core";
import { CommonModule } from "@angular/common";
import { FontAwesomeModule } from "@fortawesome/angular-fontawesome";
import { faUser } from "@fortawesome/free-solid-svg-icons";

@Component({
    selector: "teamportal-scheidsrechter",
    standalone: true,
    imports: [CommonModule, FontAwesomeModule],
    templateUrl: "./scheidsrechter.component.html",
    styleUrls: ["./scheidsrechter.component.scss"],
})
export class ScheidsrechterComponent implements OnInit {
    @Input()
    isScheidsrechter!: any;
    @Input()
    scheidsrechter!: any;

    scheidsrechterIcon = faUser;

    constructor() {}

    ngOnInit() {}
}
