import { HttpClient } from '@angular/common/http';
import { Component, OnInit } from '@angular/core';
// tslint:disable-next-line:no-implicit-dependencies
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-barcie-beschikbaarheid',
  templateUrl: './barcie-beschikbaarheid.component.html',
  styleUrls: ['./barcie-beschikbaarheid.component.scss']
})
export class BarcieBeschikbaarheidComponent implements OnInit {
  loading: boolean;
  speeldagen: any[];
  errorMessage: string;

  constructor(private httpClient: HttpClient) {}

  ngOnInit() {
    this.getBarcieBeschikbaarheid();
  }

  UpdateBarcieBeschikbaarheid(beschikbaarheid, datum, tijd) {
    this.httpClient
      .post(
        environment.baseUrl,
        {
          datum,
          tijd,
          beschikbaarheid
        },
        {
          params: { action: 'UpdateBarcieBeschikbaarheid' }
        }
      )
      .subscribe();
  }

  getBarcieBeschikbaarheid() {
    this.loading = true;
    this.httpClient
      .get<any[]>(environment.baseUrl, {
        params: {
          action: 'GetBarcieBeschikbaarheid'
        }
      })
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
