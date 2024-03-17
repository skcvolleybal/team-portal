import { Component, Input, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Email } from '../models/Email';
import { EmailsService } from '../core/services/emails.service';
import { faCheckCircle, faCogs, faExclamationCircle } from '@fortawesome/free-solid-svg-icons';

@Component({
  selector: 'tp-email-detail',
  templateUrl: './email-detail.component.html',
  styleUrls: ['./email-detail.component.scss']
})

export class EmailDetailComponent implements OnInit {
  emailId: string;
  email: Email;
  errorMessage: string;
  loading: boolean;

  check = faCheckCircle;
  cogs = faCogs;
  exclamation = faExclamationCircle;

  constructor(
    private route: ActivatedRoute,
    private EmailsService: EmailsService
    ) {}

  ngOnInit() {
    this.loading = true;
    const emailId = this.route.snapshot.paramMap.get('id');
    this.fetchEmailDetails(emailId);
  }

  fetchEmailDetails(id: string) {
    this.EmailsService.getEmailById(id).subscribe(
      data => {
        console.log(id); 
        this.loading = false;
        this.email = data[0];
      },
      error => {
        this.errorMessage = error.error.message;
        this.loading = false;  
      }
    );
  }
  

  // fetchEmailDetails

  // async getEmails() {
  //   this.loading = true;
  //   this.emailsService.getEmails().subscribe(data => {
  //     this.loading = false;
  //     this.emails = data; //  
  //     return data;
  //   }, error => {
  //     this.errorMessage = error.error.message
  //     console.error('error getting emails', error)
  //     this.loading = false
  //   })
  // }



}