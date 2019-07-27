import { Component, OnInit } from '@angular/core';
import {
  faCheck,
  faQuestion,
  faTimes
} from '@fortawesome/free-solid-svg-icons';
import { environment } from 'src/environments/environment';
import { RequestService } from '../services/RequestService';

@Component({
  templateUrl: './wedstrijden.component.html',
  styleUrls: ['./wedstrijden.component.scss']
})
export class WedstrijdenComponent implements OnInit {
  model1 = null;
  neeIcon = faTimes;
  onbekendIcon = faQuestion;
  jaIcon = faCheck;
  wedstrijden: any[];
  loading: boolean;
  errorMessage: any;

  constructor(private requestService: RequestService) {}

  getWedstrijdAanwezigheid() {
    this.requestService.GetWedstrijdAanwezigheid().subscribe(
      wedstrijden => {
        this.wedstrijden = wedstrijden;
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

  updateAanwezigheid(aanwezigheid, matchId) {
    this.requestService
      .UpdateAanwezigheid(matchId, null, aanwezigheid)
      .subscribe();
  }

  ngOnInit() {
    this.loading = true;
    this.getWedstrijdAanwezigheid();
  }
}
