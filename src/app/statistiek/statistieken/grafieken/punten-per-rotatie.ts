import { Chart } from 'chart.js';
import { IPunten } from '../models/IPunten';

// const backgroundColors = [
//     'rgba(255, 99, 132, 0.2)',
//     'rgba(54, 162, 235, 0.2)',
//     'rgba(255, 206, 86, 0.2)',
//     'rgba(75, 192, 192, 0.2)',
//     'rgba(153, 102, 255, 0.2)',
//     'rgba(255, 159, 64, 0.2)'
//   ];
//   const borderColors = [
//     'rgba(255,99,132,1)',
//     'rgba(54, 162, 235, 1)',
//     'rgba(255, 206, 86, 1)',
//     'rgba(75, 192, 192, 1)',
//     'rgba(153, 102, 255, 1)',
//     'rgba(255, 159, 64, 1)'
//   ];

export function GetGrafiekPuntenPerRotatie(punten: IPunten[], label: string) {
  return new Chart('puntenPerRotatie', {
    type: 'bar',
    data: {
      labels: punten.map((punt) => punt.type),
      datasets: [
        {
          label,
          data: punten.map((punt) => punt.percentage),
          backgroundColor: 'rgba(255, 206, 86, 0.2)',
          borderColor: 'rgba(255, 206, 86, 1)',
          borderWidth: 1,
        },
      ],
    },
    options: {
      scales: {
        yAxes: [
          {
            ticks: {
              min: 0,
              callback: (value: string) => value + '%',
            },
          },
        ],
      },
    },
  });
}
