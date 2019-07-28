import { Component, OnInit } from '@angular/core';
import {
  faCalendarCheck,
  faCheck,
  faQuestion,
  faTimes,
  faUser
} from '@fortawesome/free-solid-svg-icons';
import { AanwezigheidService } from '../services/aanwezigheid.service';

@Component({
  selector: 'app-coach-wedstrijden',
  templateUrl: './coach-wedstrijden.component.html',
  styleUrls: ['./coach-wedstrijden.component.scss']
})
export class CoachWedstrijdenComponent implements OnInit {
  neeIcon = faTimes;
  onbekendIcon = faQuestion;
  jaIcon = faCheck;
  scheidsrechterIcon = faUser;
  teamIcon = faCalendarCheck;

  loading: boolean;
  errorMessage: string;
  wedstrijden: any[];

  constructor(private aanwezigheidService: AanwezigheidService) {}

  ngOnInit() {
    this.getCoachAanwezigheid();
  }

  UpdateCoachAanwezigheid(aanwezigheid, matchId) {
    this.aanwezigheidService.UpdateCoachAanwezigheid(matchId, aanwezigheid);
  }

  getCoachAanwezigheid() {
    this.loading = true;
    this.aanwezigheidService.GetCoachAanwezigheid().subscribe(
      response => {
        this.wedstrijden = response.wedstrijden;
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
