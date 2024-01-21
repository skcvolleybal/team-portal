import { Component } from '@angular/core';
import { EmailsService } from '../core/services/emails.service';
import { Router } from '@angular/router';
import { faCheckCircle, faCogs, faExclamationCircle } from '@fortawesome/free-solid-svg-icons';


@Component({
  selector: 'tp-emails',
  templateUrl: './emails.component.html',
  styleUrls: ['./emails.component.scss']
})
export class EmailsComponent {

  emails: any;
  errorMessage: string;

  check = faCheckCircle;
  cogs = faCogs;
  exclamation = faExclamationCircle;

  loading = true;

  constructor(private emailsService: EmailsService, private router: Router) {}

  onEmailClick(emailId: number): void {
    this.router.navigate(['/emails' , emailId]);
  }


  async ngOnInit() {
    this.getEmails();
  }
  
  async getEmails() {
    this.emailsService.getEmails().subscribe(data => {
      this.emails = data; //  
      this.loading = false;
      return data;
    }, error => {
      this.errorMessage = error.error.message
      console.error('error getting emails', error)
      this.loading = false
    })
  }

}
