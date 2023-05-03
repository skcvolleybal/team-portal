import { Component, OnInit } from '@angular/core';

import { BeschikbaarheidService } from '../../core/services/beschikbaarheid.service';

@Component({
  selector: 'teamportal-fluit-beschikbaarheid',
  templateUrl: './tel-fluit-beschikbaarheid.component.html',
  styleUrls: ['./tel-fluit-beschikbaarheid.component.scss'],
})
export class TelFluitBeschikbaarheidComponent implements OnInit {
  loading: boolean;
  speeldagen: any[];
  errorMessage: string;
  speeldagenEmpty: boolean = false;

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
      (speeldagen) => {
        this.speeldagen = speeldagen;
        this.loading = false;
        // If speeldagen is empty we display some text so that the user knows there is not an error.
        if (this.speeldagen.length == 0) {
          this.speeldagenEmpty = true;
        }
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
