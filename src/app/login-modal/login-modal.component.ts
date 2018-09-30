import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';

@Component({
   selector: 'app-login-modal',
   templateUrl: './login-modal.component.html',
   styleUrls: ['./login-modal.component.scss']
})
export class LoginModalComponent implements OnInit {

   constructor(private httpClient: HttpClient) { }

   username: string;
   password: string;

   login() {
      this.httpClient.post<any>('https://www.skcvolleybal.nl/script/team-portal/php/interface/php?action=Login', {
         username: this.username,
         password: this.password
      }, {
         withCredentials: true
      }).subscribe(x => console.log(x));
   }

   ngOnInit() {
   }

}
