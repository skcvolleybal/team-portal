import { Component } from '@angular/core';
import { StatisticsService } from '../core/services/statistics.service';

@Component({
  selector: 'tp-statistieken',
  templateUrl: './statistieken.component.html',
  styleUrls: ['./statistieken.component.scss']
})
export class StatistiekenComponent {
  skcRankingData: any; // Assuming you will replace 'any' with a more specific type
  loading: boolean;
  errorMessage: string;


  constructor(private statisticsService: StatisticsService) {}

  async ngOnInit() {
    this.getSkcRankingData();
  }
  
  async getSkcRankingData() {
    this.loading = true;
    this.statisticsService.getSkcRanking().subscribe(data => {
      this.loading = false;
      this.skcRankingData = data; // Store data in skcRankingData property
      return data;
    }, error => {
      console.error('error getting stats data', error);
      this.errorMessage = error.error.message;
      this.loading = false;
    })
  }
  
  
}
