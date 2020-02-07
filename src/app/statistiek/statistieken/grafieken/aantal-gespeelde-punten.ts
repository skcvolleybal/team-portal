import { Chart } from 'chart.js';

export function AantalGespeeldePunten(spelers: any[]) {
  return new Chart('aantalGespeeldePunten', {
    type: 'bar',
    data: {
      datasets: [
        {
          label: 'Aantal gespeelde punten',
          data: spelers.map(speler => speler.aantalGespeeldePunten),
          backgroundColor: 'rgba(54, 162, 235, 0.2)',
          borderColor: 'rgba(54, 162, 235, 1)',
          borderWidth: 1
        }
      ]
    },
    options: {
      scales: {
        xAxes: [
          {
            labels: spelers.map(speler => speler.voornaam)
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
}
