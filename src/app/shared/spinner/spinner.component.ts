import { Component, Input, OnInit } from "@angular/core";
import { CommonModule } from "@angular/common";

@Component({
    selector: "teamportal-spinner",
    standalone: true,
    imports: [CommonModule],
    templateUrl: "./spinner.component.html",
    styleUrls: ["./spinner.component.scss"],
})
export class SpinnerComponent implements OnInit {
    constructor() {}

    ngOnInit() {}
}
