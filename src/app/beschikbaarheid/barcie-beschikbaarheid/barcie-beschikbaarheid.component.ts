import { Component, OnInit } from '@angular/core';

import { BeschikbaarheidService } from '../../core/services/beschikbaarheid.service';

@Component({
  selector: 'teamportal-barcie-beschikbaarheid',
  templateUrl: './barcie-beschikbaarheid.component.html',
  styleUrls: ['./barcie-beschikbaarheid.component.scss'],
})
export class BarcieBeschikbaarheidComponent implements OnInit {
  loading: boolean;
  speeldagen: any[];
  errorMessage: string;

  constructor(private beschikbaarheidService: BeschikbaarheidService) {}

  ngOnInit() {
    this.getBarcieBeschikbaarheid();
  }

  UpdateBarcieBeschikbaarheid(beschikbaarheid, date) {
    this.beschikbaarheidService
      .UpdateBarcieBeschikbaarheid(date, beschikbaarheid)
      .subscribe();
  }

  getBarcieBeschikbaarheid() {
    this.loading = true;
    this.beschikbaarheidService.GetBarcieBeschikbaarheid().subscribe(
      (speeldagen) => {
        this.speeldagen = speeldagen;
        this.loading = false;
      },
      (error) => {
        if (error.status === 500) {
          this.errorMessage = error.error.message;
          this.loading = false;
        }
      }
    );
  }
}
