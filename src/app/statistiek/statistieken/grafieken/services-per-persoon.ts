import { Chart } from 'chart.js';
import { IPunten } from '../models/IPunten';

export function SetServicesGraph(services: IPunten[]) {
  return new Chart('servicesPerPersoon', {
    type: 'bar',
    data: {
      labels: services.map((punt) => punt.voornaam),
      datasets: [
        {
          label: 'Winstpercentage',
          yAxisID: 'percentage',
          data: services.map((punt) => punt.percentage),
          backgroundColor: 'rgba(75, 192, 192, 0.2)',
          borderColor: 'rgba(75, 192, 192, 1)',
          borderWidth: 1,
        },
        {
          label: 'Aantal services',
          yAxisID: 'aantal',
          data: services.map((punt) => punt.totaalPunten),
          backgroundColor: 'rgba(153, 102, 255, 0.2)',
          borderColor: 'rgba(153, 102, 255, 1)',
          borderWidth: 1,
        },
      ],
    },
    options: {
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
              stepSize: 20,
            },
          },
          {
            type: 'linear',
            ticks: {
              stepSize:
                Math.round(
                  Math.max.apply(
                    Math,
                    services.map((service) => service.totaalPunten)
                  ) / 10
                ) * 2,
            },
            display: true,
            position: 'right',
            id: 'aantal',
            gridLines: {
              drawOnChartArea: false,
            },
          },
        ],
      },
    },
  });
}
