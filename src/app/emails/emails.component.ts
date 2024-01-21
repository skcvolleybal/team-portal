import { Component } from '@angular/core';
import { EmailsService } from '../core/services/emails.service';
import { Router } from '@angular/router';
import { faChevronCircleRight } from '@fortawesome/free-solid-svg-icons';


@Component({
  selector: 'tp-emails',
  templateUrl: './emails.component.html',
  styleUrls: ['./emails.component.scss']
})
export class EmailsComponent {

  emails: any;
  loading: boolean;
  errorMessage: string;

  chevronRight = faChevronCircleRight;
  

  constructor(private emailsService: EmailsService, private router: Router) {}

  onEmailClick(emailId: number): void {
    this.router.navigate(['/emails' , emailId]);
  }


  async ngOnInit() {
    this.getEmails();
  }
  
  async getEmails() {
    this.loading = true;
    this.emailsService.getEmails().subscribe(data => {
      this.loading = false;
      this.emails = data; //  
      return data;
    }, error => {
      this.errorMessage = error.error.message
      console.error('error getting emails', error)
      this.loading = false
    })
  }

}
