import { Chart } from 'chart.js';
import { IPunten } from '../models/IPunten';

export function SetPlusminusGraph(plusminus: IPunten[], label: string) {
  return new Chart('plusminusPerPersoon', {
    type: 'bar',
    data: {
      labels: plusminus.map(punt => punt.voornaam),
      datasets: [
        {
          label: 'Winstpercentage',
          yAxisID: 'percentage',
          data: plusminus.map(punt => punt.percentage),
          backgroundColor: 'rgba(153, 102, 255, 0.2)',
          borderColor: 'rgba(153, 102, 255, 1)',
          borderWidth: 1
        },
        {
          label: 'Plusminus (over 50 punten)',
          yAxisID: 'plusminus',
          data: plusminus.map(punt => punt.plusminus),
          backgroundColor: 'rgba(255, 159, 64, 0.2)',
          borderColor: 'rgba(255, 159, 64, 1)',
          borderWidth: 1
        }
      ]
    },
    options: {
      title: {
        display: true,
        text: 'Gespeelde punten'
      },
      scales: {
        yAxes: [
          {
            type: 'linear',
            display: true,
            position: 'left',
            id: 'percentage',
            ticks: {
              min: 0,
              callback: (value: string) => value + '%',
              stepSize: 20
            }
          },
          {
            type: 'linear',
            ticks: {
              stepSize: 10
            },
            display: true,
            position: 'right',
            id: 'plusminus',
            gridLines: {
              drawOnChartArea: false
            }
          }
        ]
      }
    }
  });
}
