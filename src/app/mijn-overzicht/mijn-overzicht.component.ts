import { HttpClient } from '@angular/common/http';
import { Component, OnInit } from '@angular/core';
import {
  faCalendarCheck,
  faMinusSquare,
  faPlusSquare,
  faUser
} from '@fortawesome/free-solid-svg-icons';
import { Observable } from 'rxjs/internal/Observable';
// tslint:disable-next-line:no-implicit-dependencies
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-mijn-overzicht',
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
  isWebcie: boolean;

  constructor(private http: HttpClient) {}

  getMijnOverzicht(): Observable<{ overzicht: any; isWebcie: boolean }> {
    return this.http.get<{ overzicht: any; isWebcie: boolean }>(
      environment.baseUrl,
      {
        params: {
          action: 'GetMijnOverzicht'
        }
      }
    );
  }

  ngOnInit() {
    this.loading = true;
    this.getMijnOverzicht().subscribe(
      response => {
        this.dagen = response.overzicht;
        this.isWebcie = response.isWebcie;
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
