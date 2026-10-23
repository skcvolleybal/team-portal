import { Component, Input, OnInit } from "@angular/core";
import { CommonModule } from "@angular/common";
import { FontAwesomeModule } from "@fortawesome/angular-fontawesome";
import { ActivatedRoute } from "@angular/router";
import { Email } from "../models/Email";
import { EmailsService } from "../core/services/emails.service";
import {
    faCheckCircle,
    faCogs,
    faExclamationCircle,
} from "@fortawesome/free-solid-svg-icons";

@Component({
    selector: "tp-email-detail",
    standalone: true,
    imports: [CommonModule, FontAwesomeModule],
    templateUrl: "./email-detail.component.html",
    styleUrls: ["./email-detail.component.scss"],
})
export class EmailDetailComponent implements OnInit {
    emailId!: string;
    email!: Email;
    errorMessage!: string;
    loading!: boolean;

    check = faCheckCircle;
    cogs = faCogs;
    exclamation = faExclamationCircle;

    constructor(
        private route: ActivatedRoute,
        private EmailsService: EmailsService,
    ) {}

    ngOnInit() {
        this.loading = true;
        const emailId = this.route.snapshot.paramMap.get("id");
        if (emailId) {
            this.fetchEmailDetails(emailId);
        }
    }

    fetchEmailDetails(id: string) {
        this.EmailsService.getEmailById(id).subscribe(
            (data) => {
                console.log(id);
                this.loading = false;
                this.email = data;
            },
            (error) => {
                this.errorMessage = error.error.message;
                this.loading = false;
            },
        );
    }
}
