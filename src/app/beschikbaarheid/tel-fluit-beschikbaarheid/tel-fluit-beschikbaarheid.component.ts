import { Component, OnInit } from '@angular/core';
import { BeschikbaarheidService } from '../../core/services/beschikbaarheid.service';

@Component({
  selector: 'teamportal-fluit-beschikbaarheid',
  templateUrl: './tel-fluit-beschikbaarheid.component.html',
  styleUrls: ['./tel-fluit-beschikbaarheid.component.scss']
})
export class TelFluitBeschikbaarheidComponent implements OnInit {
  loading: boolean;
  speeldagen: any[];
  errorMessage: string;

  constructor(private beschikbaarheidService: BeschikbaarheidService) {}

  ngOnInit() {
    this.getFluitBeschikbaarheid();
  }

  UpdateFluitBeschikbaarheid(beschikbaarheid, datum, tijd) {
    this.beschikbaarheidService
      .UpdateFluitBeschikbaarheid(datum, tijd, beschikbaarheid)
      .subscribe();
  }

  getFluitBeschikbaarheid() {
    this.loading = true;
    this.beschikbaarheidService.GetFluitBeschikbaarheid().subscribe(
      speeldagen => {
        this.speeldagen = speeldagen;
        this.loading = false;
      },
      error => {
        if (error.status === 500) {
          this.errorMessage = error.error.message;
          this.loading = false;
        }
      }
    );
  }
}
