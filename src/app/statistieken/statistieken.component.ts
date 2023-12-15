import { Component } from '@angular/core';
import { StatisticsService } from '../core/services/statistics.service';

@Component({
  selector: 'tp-statistieken',
  templateUrl: './statistieken.component.html',
  styleUrls: ['./statistieken.component.scss']
})
export class StatistiekenComponent {
  skcRankingData: any; 

  constructor (private statisticsService: StatisticsService) {} 

  ngOnInit() {
    this.getSkcRankingData();
  }

  getSkcRankingData () {
    this.statisticsService.getSkcRanking().subscribe(data => {
      this.getSkcRankingData = data;
    }, error => {
      console.error('error getting stats data', error);
     })
  }


}
