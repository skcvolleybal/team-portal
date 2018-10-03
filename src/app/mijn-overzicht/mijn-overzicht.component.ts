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

  constructor(private http: HttpClient) {}

  getMijnOverzicht(): Observable<any[]> {
    return this.http.get<any[]>(
      environment.baseUrl + 'php/interface.php?action=GetMijnOverzicht'
    );
  }

  ngOnInit() {
    this.loading = true;
    this.getMijnOverzicht().subscribe(
      overzicht => (this.dagen = overzicht),
      () => {},
      () => (this.loading = false)
    );
  }
}
