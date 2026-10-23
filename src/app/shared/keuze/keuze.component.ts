import { Component, EventEmitter, Input, Output } from "@angular/core";
import { CommonModule } from "@angular/common";
import { FontAwesomeModule } from "@fortawesome/angular-fontawesome";
import {
    faCheck,
    faQuestion,
    faTimes,
} from "@fortawesome/free-solid-svg-icons";
import { ToastService } from "ngx-yet-another-toast-library";

@Component({
    selector: "teamportal-keuze",
    standalone: true,
    imports: [CommonModule, FontAwesomeModule],
    templateUrl: "./keuze.component.html",
    styleUrls: ["./keuze.component.scss"],
})
export class KeuzeComponent {
    constructor(private toast: ToastService) {}

    @Input()
    keuze!: any;
    @Output()
    updateKeuze = new EventEmitter();

    jaIcon = faCheck;
    onbekendIcon = faQuestion;
    neeIcon = faTimes;

    onClick(keuze: any) {
        this.keuze = keuze;
        this.updateKeuze.emit(keuze);
        this.toast.success(" ", "Saving..", {
            duration: 750,
            progressBar: true,
        });
    }
}
