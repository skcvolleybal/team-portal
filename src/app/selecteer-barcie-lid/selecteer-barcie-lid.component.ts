import { HttpClient } from '@angular/common/http';
import { Component, OnInit } from '@angular/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
// tslint:disable-next-line:no-implicit-dependencies
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-selecteer-barcie-lid',
  templateUrl: './selecteer-barcie-lid.component.html',
  styleUrls: ['./selecteer-barcie-lid.component.scss']
})
export class SelecteerBarcieLidComponent implements OnInit {
  static date: string;
  static shift: number;
  static datum: string;

  date: string;
  datum: string;
  shift: number;
  barcieLeden: any[];
  barcieLedenLoading: boolean;
  errorMessage: string;

  constructor(public modal: NgbActiveModal, private httpClient: HttpClient) {}

  ngOnInit() {
    this.date = SelecteerBarcieLidComponent.date;
    this.datum = SelecteerBarcieLidComponent.datum;
    this.shift = SelecteerBarcieLidComponent.shift;
    this.GetBarcieLeden();
  }

  GetBarcieLeden() {
    this.barcieLedenLoading = true;
    this.httpClient
      .get<any>(environment.baseUrl, {
        params: {
          action: 'GetBarcieLeden',
          date: this.date
        }
      })
      .subscribe(
        response => {
          this.barcieLedenLoading = false;
          this.barcieLeden = response.barcieLeden;
        },
        response => {
          this.barcieLedenLoading = false;
          this.errorMessage = response.error;
        }
      );
  }

  AddBarcieAanwezigheid(barcieLid) {
    this.httpClient
      .post<any>(
        environment.baseUrl,
        {
          date: this.date,
          shift: this.shift,
          barcieLidId: barcieLid.id
        },
        {
          params: {
            action: 'AddBarcieAanwezigheid'
          }
        }
      )
      .subscribe(
        () => {
          this.modal.close(barcieLid);
        },
        response => {
          this.errorMessage = response.error;
        }
      );
  }
}
