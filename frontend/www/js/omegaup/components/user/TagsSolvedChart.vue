<template>
  <div class="tags-solved-chart">
    <h5 class="chart-title">{{ T.profileTagsSolved }}</h5>
    <div v-if="tags.length > 0" class="chart-container">
      <highcharts :options="chartOptions"></highcharts>
    </div>
    <div v-else class="no-data">
      <span>{{ T.profileNoTagsData }}</span>
    </div>
  </div>
</template>

<script lang="ts">
import { Chart } from 'highcharts-vue';
import * as Highcharts from 'highcharts/highstock';
import { Component, Prop, Vue } from 'vue-property-decorator';
import T from '../../lang';

interface TagStats {
  name: string;
  count: number;
}

@Component({
  components: {
    highcharts: Chart,
  },
})
export default class TagsSolvedChart extends Vue {
  @Prop({ required: true }) tags!: TagStats[];

  T = T;

  // Color palette for tags
  private readonly colors = [
    '#ff7675',
    '#74b9ff',
    '#55efc4',
    '#ffeaa7',
    '#a29bfe',
    '#fd79a8',
    '#81ecec',
    '#fab1a0',
    '#dfe6e9',
    '#00cec9',
    '#e17055',
    '#0984e3',
    '#00b894',
    '#fdcb6e',
    '#6c5ce7',
    '#e84393',
    '#00b8a3',
    '#d63031',
  ];

  get chartOptions(): Highcharts.Options {
    const chartData = this.tags.slice(0, 18).map((tag, index) => ({
      name: tag.name,
      y: tag.count,
      color: this.colors[index % this.colors.length],
    }));

    return {
      chart: {
        type: 'pie',
        backgroundColor: 'transparent',
        height: 300,
      },
      title: {
        text: '',
      },
      tooltip: {
        formatter: function (): string {
          const point = (this as unknown) as Highcharts.Point;
          return `<b>${point.y}</b> ${T.profileProblemsCount}`;
        },
      },
      plotOptions: {
        pie: {
          innerSize: '60%',
          dataLabels: {
            enabled: false,
          },
          showInLegend: true,
        },
      },
      legend: {
        enabled: true,
        layout: 'vertical',
        align: 'right',
        verticalAlign: 'middle',
        itemStyle: {
          color: '#666',
          fontSize: '12px',
        },
        labelFormatter: function (
          this: Highcharts.Point | Highcharts.Series,
        ): string {
          if ('y' in this && typeof this.y === 'number') {
            return `${this.name}: ${this.y}`;
          }
          return this.name;
        },
      },
      credits: {
        enabled: false,
      },
      series: [
        {
          type: 'pie',
          name: 'Tags',
          data: chartData,
        },
      ],
    };
  }
}
</script>

<style lang="scss" scoped>
.tags-solved-chart {
  background-color: #fff;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  min-height: 340px;
  height: 100%;
  width: 100%;
}

.chart-title {
  font-size: 1rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 15px;
}

.chart-container {
  min-height: 300px;
}

.no-data {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 200px;
  color: #999;
  font-style: italic;
}
</style>
