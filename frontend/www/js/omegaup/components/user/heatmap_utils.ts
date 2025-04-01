import * as Highcharts from 'highcharts/highstock';

interface HeatmapColors {
  emptyCell: string;
  lowActivity: string;
  mediumActivity: string;
  highActivity: string;
  background: string;
  wrapper: string;
}

interface HeatmapChartParams {
  heatmapContainer: HTMLElement;
  formattedData: Array<[number, number, number]>;
  startDate: Date;
  firstDayOffset: number;
  totalWeeks: number;
  boxWidth: number;
  boxHeight: number;
  boxPadding: number;
  chartWidth: number;
  chartHeight: number;
  colors: HeatmapColors;
}

/**
 * Get Highcharts options for the user activity heatmap
 * @param params - Parameters for chart configuration
 * @returns Highcharts.Options object
 */
export function getHeatmapChartOptions(
  params: HeatmapChartParams,
): Highcharts.Options {
  const {
    heatmapContainer,
    formattedData,
    startDate,
    firstDayOffset,
    totalWeeks,
    boxWidth,
    boxHeight,
    boxPadding,
    chartWidth,
    chartHeight,
    colors,
  } = params;

  return {
    chart: {
      renderTo: heatmapContainer,
      type: 'heatmap',
      height: chartHeight,
      width: chartWidth,
      spacing: [0, 0, 10, 0],
      margin: [0, 0, 15, 0],
      backgroundColor: colors.background,
    },
    title: {
      text: '',
    },
    subtitle: {
      text: '',
    },
    xAxis: {
      min: 0,
      max: totalWeeks,
      labels: {
        enabled: true,
        formatter: function () {
          const weekFirstDay = new Date(startDate);
          weekFirstDay.setDate(
            weekFirstDay.getDate() + (this.value as number) * 7,
          );

          if (weekFirstDay.getDate() <= 7) {
            return weekFirstDay.toLocaleString('default', {
              month: 'short',
            });
          }
          return '';
        },
        style: {
          fontSize: '9px',
          fontWeight: 'bold',
        },
        y: 7,
        align: 'left',
      },
      lineWidth: 0,
      tickWidth: 0,
      tickPositioner: function () {
        const positions = [];
        for (let month = 0; month < 12; month++) {
          const monthFirstDay = new Date(startDate.getFullYear(), month, 1);
          const dayOfYear = Math.floor(
            (monthFirstDay.getTime() - startDate.getTime()) /
              (24 * 60 * 60 * 1000),
          );
          const weekNumber = Math.floor((dayOfYear + firstDayOffset) / 7);
          positions.push(weekNumber);
        }
        return positions;
      },
    },
    yAxis: {
      min: 0,
      max: 6,
      labels: {
        enabled: false,
      },
      lineWidth: 0,
      tickWidth: 0,
    },
    colorAxis: {
      dataClasses: [
        { from: -1, to: 0, color: colors.emptyCell },
        { from: 1, to: 4, color: colors.lowActivity },
        { from: 5, to: 9, color: colors.mediumActivity },
        { from: 10, to: 1000, color: colors.highActivity },
      ],
      labels: {
        enabled: false,
      },
    },
    tooltip: {
      formatter: function () {
        const date = new Date(startDate);
        const dayOffset =
          (this.point.x as number) * 7 +
          (this.point.y as number) -
          firstDayOffset;
        date.setDate(date.getDate() + dayOffset);

        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const formattedDate = `${year}-${month}-${day}`;

        const value = this.point.value as number;
        if (value > 0) {
          return `<b>${formattedDate}</b><br>Total submissions: <b>${value}</b>`;
        }
        return `<b>${formattedDate}</b>`;
      },
    },
    legend: {
      enabled: false,
    },
    credits: {
      enabled: false,
    },
    series: [
      {
        name: 'Submissions',
        borderWidth: 0.1,
        borderColor: '#ffffff',
        data: formattedData,
        dataLabels: {
          enabled: false,
        },
        type: 'heatmap' as const,
        pointWidth: boxWidth,
        pointHeight: boxHeight,
        pointPadding: boxPadding / 2,
        states: {
          hover: {
            brightness: 0.1,
            borderColor: '#ffffff',
          },
        },
      } as Highcharts.SeriesHeatmapOptions,
    ],
  };
}
