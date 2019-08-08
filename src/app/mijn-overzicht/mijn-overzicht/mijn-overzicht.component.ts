import { Component, OnInit } from '@angular/core';
import {
  faCalendarCheck,
  faPlusSquare,
  faUser
} from '@fortawesome/free-solid-svg-icons';
import { RequestService } from '../../core/services/request.service';

@Component({
  selector: 'teamportal-mijn-overzicht',
  templateUrl: './mijn-overzicht.component.html',
  styleUrls: ['./mijn-overzicht.component.scss']
})
export class MijnOverzichtComponent implements OnInit {
  loading: boolean;
  scheidsrechterIcon = faUser;
  tellersIcon = faCalendarCheck;
  openIcon = faPlusSquare;
  dagen: any[];
  errorMessage: string;

  constructor(private requestService: RequestService) {}

  ngOnInit() {
    this.loading = true;
    this.requestService.GetMijnOverzicht().subscribe(
      response => {
        this.dagen = response;
        this.loading = false;
      },
      error => {
        if (error.status === 500) {
          this.errorMessage = error.error;
          this.loading = false;
        }
      }
    );
  }
}
