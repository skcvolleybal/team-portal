import { HttpClient } from '@angular/common/http';
import { Component, OnInit } from '@angular/core';
import { Observable } from 'rxjs/internal/Observable';
import { environment } from '../../environments/environment';

@Component({
  selector: 'app-fluit-beschikbaarheid',
  templateUrl: './fluit-beschikbaarheid.component.html',
  styleUrls: ['./fluit-beschikbaarheid.component.scss']
})
export class FluitBeschikbaarheidComponent implements OnInit {
  loading: boolean;
  speeldagen: any[];
  errorMessage: string;

  constructor(private httpClient: HttpClient) {}

  ngOnInit() {
    this.getFluitBeschikbaarheid();
  }

  UpdateFluitBeschikbaarheid(beschikbaarheid, datum, tijd) {
    this.httpClient
      .post(
        environment.baseUrl +
          'php/interface.php?action=UpdateFluitBeschikbaarheid',
        {
          datum,
          tijd,
          beschikbaarheid
        }
      )
      .subscribe();
  }

  getFluitBeschikbaarheid() {
    this.loading = true;
    this.httpClient
      .get<any[]>(
        environment.baseUrl + 'php/interface.php?action=GetFluitOverzicht'
      )
      .subscribe(
        speeldagen => {
          this.speeldagen = speeldagen;
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
