import { Component, OnInit, ViewChild } from '@angular/core';
import { Chart } from 'chart.js';
import { RequestService } from '../services/RequestService';

@Component({
  selector: 'app-statistieken',
  templateUrl: './statistieken.component.html',
  styleUrls: ['./statistieken.component.scss']
})
export class StatistiekenComponent implements OnInit {
  @ViewChild('gespeeldePunten', { static: true }) private gespeeldePuntenCanvas;

  kleuren = ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'];
  aantalSpelers: number;
  myChart: any;

  constructor(private requestService: RequestService) {}

  ngOnInit() {
    this.drawGespeeldePunten();
  }

  drawGespeeldePunten() {
    this.requestService.GetGespeeldePunten().subscribe(spelers => {
      this.aantalSpelers = spelers.length;

      const backgroundColors = [
        'rgba(255, 99, 132, 0.2)',
        'rgba(54, 162, 235, 0.2)',
        'rgba(255, 206, 86, 0.2)',
        'rgba(75, 192, 192, 0.2)',
        'rgba(153, 102, 255, 0.2)',
        'rgba(255, 159, 64, 0.2)'
      ];
      const borderColors = [
        'rgba(255,99,132,1)',
        'rgba(54, 162, 235, 1)',
        'rgba(255, 206, 86, 1)',
        'rgba(75, 192, 192, 1)',
        'rgba(153, 102, 255, 1)',
        'rgba(255, 159, 64, 1)'
      ];
      this.myChart = new Chart(this.gespeeldePuntenCanvas.nativeElement, {
        type: 'bar',
        data: {
          datasets: [
            {
              label: 'Aantal gespeelde punten',
              data: spelers.map(speler => speler.gespeeldePunten),
              backgroundColor: backgroundColors,
              borderColor: borderColors,
              borderWidth: 1
            }
          ]
        },
        options: {
          scales: {
            xAxes: [
              {
                labels: spelers.map(speler => speler.naam)
              }
            ],
            yAxes: [
              {
                ticks: {
                  beginAtZero: true
                }
              }
            ]
          }
        }
      });
    });
  }
}
