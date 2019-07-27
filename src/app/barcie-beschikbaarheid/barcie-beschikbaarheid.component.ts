import { Component, OnInit } from '@angular/core';
import { RequestService } from '../services/RequestService';

@Component({
  selector: 'app-barcie-beschikbaarheid',
  templateUrl: './barcie-beschikbaarheid.component.html',
  styleUrls: ['./barcie-beschikbaarheid.component.scss']
})
export class BarcieBeschikbaarheidComponent implements OnInit {
  loading: boolean;
  speeldagen: any[];
  errorMessage: string;

  constructor(private requestService: RequestService) {}

  ngOnInit() {
    this.getBarcieBeschikbaarheid();
  }

  UpdateBarcieBeschikbaarheid(beschikbaarheid, date) {
    this.requestService
      .UpdateBarcieBeschikbaarheid(date, beschikbaarheid)
      .subscribe();
  }

  getBarcieBeschikbaarheid() {
    this.loading = true;
    this.requestService.GetBarcieBeschikbaarheid().subscribe(
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
