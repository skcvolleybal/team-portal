import { Component } from '@angular/core';
import { StatisticsService } from '../core/services/statistics.service';

@Component({
  selector: 'tp-statistieken',
  templateUrl: './statistieken.component.html',
  styleUrls: ['./statistieken.component.scss']
})
export class StatistiekenComponent {
  skcRankingData: any; // Assuming you will replace 'any' with a more specific type
  skcFustInformatie: string;
  loading: boolean;

  constructor(private statisticsService: StatisticsService) {}

  async ngOnInit() {
    await this.getSkcRankingData();
    this.generateSkcFustInformatie();
  }
  
  async getSkcRankingData() {
    this.loading = true;
    this.statisticsService.getSkcRanking().subscribe(data => {
      this.loading = false;
      this.skcRankingData = data; // Store data in skcRankingData property
      return data;
    }, error => {
      console.error('error getting stats data', error);
    })
  }
  
  

  

  generateSkcFustInformatie () {
    console.log("Generating fust" + this.skcRankingData);
  }
}
