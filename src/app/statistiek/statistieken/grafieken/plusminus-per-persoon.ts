import { Chart } from 'chart.js';
import { IPunten } from '../models/IPunten';

export function SetPlusminusGraph(plusminus: IPunten[], label: string) {
  const datasets = [
    {
      label: 'Winstpercentage',
      yAxisID: 'percentage',
      data: plusminus.map((punt) => punt.percentage),
      backgroundColor: 'rgba(153, 102, 255, 0.2)',
      borderColor: 'rgba(153, 102, 255, 1)',
      borderWidth: 1,
    },
    {
      label: 'Plusminus (over 50 punten)',
      yAxisID: 'plusminus',
      data: plusminus.map((punt) => punt.plusminus),
      backgroundColor: 'rgba(255, 159, 64, 0.2)',
      borderColor: 'rgba(255, 159, 64, 1)',
      borderWidth: 1,
    },
  ];
  const options = {
    scales: {
      yAxes: [
        {
          type: 'linear',
          display: true,
          position: 'left',
          id: 'percentage',
          ticks: {
            min: 0,
            callback: (value: number) => (value % 1 === 0 ? value + '%' : ''),
            stepSize: 20,
          },
        },
        {
          type: 'linear',
          ticks: {
            stepSize: 10,
          },
          display: true,
          position: 'right',
          id: 'plusminus',
          gridLines: {
            drawOnChartArea: false,
          },
        },
      ],
    },
  };
  scaleDataAxesToUnifyZeroes(datasets, options);

  return new Chart('plusminusPerPersoon', {
    type: 'bar',
    data: {
      labels: plusminus.map((punt) => punt.voornaam),
      datasets,
    },
    options,
  });
}

function scaleDataAxesToUnifyZeroes(datasets, options) {
  const axes = options.scales.yAxes;
  // Determine overall max/min values for each dataset
  datasets.forEach((line) => {
    const axis = line.yAxisID
      ? axes.filter((ax) => ax.id === line.yAxisID)[0]
      : axes[0];
    axis.min_value = FindClosest10(
      Math.min(...line.data, axis.min_value || 0),
      false
    );
    axis.max_value = FindClosest10(
      Math.max(...line.data, axis.max_value || 0),
      true
    );
  });
  // Which gives the overall range of each axis
  axes.forEach((axis) => {
    axis.range = axis.max_value - axis.min_value;
    // Express the min / max values as a fraction of the overall range
    axis.min_ratio = axis.min_value / axis.range;
    axis.max_ratio = axis.max_value / axis.range;
  });
  // Find the largest of these ratios
  const largest = axes.reduce((a, b) => ({
    min_ratio: Math.min(a.min_ratio, b.min_ratio),
    max_ratio: Math.max(a.max_ratio, b.max_ratio),
  }));
  // Then scale each axis accordingly
  axes.forEach((axis) => {
    axis.ticks = axis.ticks || {};
    axis.ticks.min = largest.min_ratio * axis.range;
    axis.ticks.max = largest.max_ratio * axis.range;
  });
}

function FindClosest10(x: number, isClosest10Higher: boolean): number {
  return Math.round((x + (isClosest10Higher ? 5 : -5)) / 10) * 10;
}
