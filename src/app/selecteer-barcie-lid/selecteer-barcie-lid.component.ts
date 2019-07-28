import { Component, OnInit } from '@angular/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { AanwezigheidService } from '../services/aanwezigheid.service';
import { BarcoService } from '../services/barco.service';

@Component({
  selector: 'app-selecteer-barcie-lid',
  templateUrl: './selecteer-barcie-lid.component.html',
  styleUrls: ['./selecteer-barcie-lid.component.scss']
})
export class SelecteerBarcielidComponent implements OnInit {
  static date: string;
  static shift: number;
  static datum: string;

  date: string;
  datum: string;
  shift: number;
  barcieLeden: any[];
  barcieLedenLoading: boolean;
  errorMessage: string;

  constructor(
    public modal: NgbActiveModal,
    private barcoService: BarcoService,
    private aanwezigheidService: AanwezigheidService
  ) {}

  ngOnInit() {
    this.date = SelecteerBarcielidComponent.date;
    this.datum = SelecteerBarcielidComponent.datum;
    this.shift = SelecteerBarcielidComponent.shift;
    this.GetBarcieLeden();
  }

  GetBarcieLeden() {
    this.barcieLedenLoading = true;
    this.barcoService.GetBarcieleden(this.date).subscribe(
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

  AddBarcieAanwezigheid(barcielid) {
    this.aanwezigheidService
      .AddBarcieAanwezigheid(this.date, this.shift, barcielid.id)
      .subscribe(
        () => {
          this.modal.close(barcielid);
        },
        response => {
          this.errorMessage = response.error;
        }
      );
  }
}
