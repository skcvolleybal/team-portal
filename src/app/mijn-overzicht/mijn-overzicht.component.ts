import { HttpClient } from '@angular/common/http';
import { Component, OnInit } from '@angular/core';
import {
  faCalendarCheck,
  faMinusSquare,
  faPlusSquare,
  faUser
} from '@fortawesome/free-solid-svg-icons';
import { Observable } from 'rxjs/internal/Observable';

@Component({
  selector: 'app-mijn-overzicht',
  templateUrl: './mijn-overzicht.component.html',
  styleUrls: ['./mijn-overzicht.component.scss']
})
export class MijnOverzichtComponent implements OnInit {
  scheidsrechterIcon = faUser;
  tellersIcon = faCalendarCheck;
  openIcon = faPlusSquare;
  dagen: any[];

  constructor(private http: HttpClient) {}

  getMijnOverzicht(): Observable<any[]> {
    return this.http.get<any[]>(
      'https://www.skcvolleybal.nl/scripts/team-portal/php/interface.php?action=GetMijnOverzicht',
      {
        withCredentials: true
      }
    );
  }

  ngOnInit() {
    this.getMijnOverzicht().subscribe(overzicht => {
      console.log(overzicht);
      this.dagen = overzicht;
    });
  }
}
