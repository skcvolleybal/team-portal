import { Component } from '@angular/core';
import { EmailsService } from '../core/services/emails.service';
import { Router } from '@angular/router';
import { faCheckCircle, faCogs, faExclamationCircle } from '@fortawesome/free-solid-svg-icons';
import { formatDate } from '@angular/common';


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

  prepareEmails(emails) {
    let previousDate = null;
    for (let email of emails) {
      const currentDate = formatDate(email.send_date, 'shortDate', 'en-US');
      email.isNewDay = currentDate !== previousDate;
      previousDate = currentDate;
    }  
  }
  
  async getEmails() {
    this.emailsService.getEmails().subscribe(data => {
      this.emails = data; //  
      this.loading = false;
      this.prepareEmails(this.emails);
      return data;
    }, error => {
      this.errorMessage = error.error.message
      console.error('error getting emails', error)
      this.loading = false
    })
  }

}
