import { Component, OnInit } from '@angular/core';
import {
  faCheck,
  faQuestion,
  faTimes
} from '@fortawesome/free-solid-svg-icons';
import { AanwezigheidService } from '../../core/services/aanwezigheid.service';

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

  constructor(private aanwezigheidService: AanwezigheidService) {}

  getWedstrijdAanwezigheid() {
    this.aanwezigheidService.GetWedstrijdAanwezigheid().subscribe(
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

  updateAanwezigheid(aanwezigheid, wedstrijd) {
    const rol = wedstrijd.isEigenWedstrijd ? 'speler' : 'coach';
    this.aanwezigheidService.UpdateAanwezigheid(
      wedstrijd.id,
      null,
      aanwezigheid,
      rol
    );
  }

  ngOnInit() {
    this.loading = true;
    this.getWedstrijdAanwezigheid();
  }
}
