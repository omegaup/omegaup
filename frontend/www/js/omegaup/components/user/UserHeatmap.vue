<template>
  <div class="user-heatmap-container">
    <div class="user-heatmap-wrapper">
      <div class="year-selector">
        <select
          v-model="selectedYear"
          class="form-control"
          @change="updateHeatmap"
        >
          <option v-for="year in availableYears" :key="year" :value="year">
            {{ year }}
          </option>
        </select>
      </div>
      <div ref="heatmapContainer" class="user-heatmap"></div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { Chart } from 'highcharts-vue';
import * as Highcharts from 'highcharts/highstock';
import HighchartsHeatmap from 'highcharts/modules/heatmap';
import T from '../../lang';
import * as ui from '../../ui';
import { apiCall } from '../../api';

HighchartsHeatmap(Highcharts);

interface HeatmapDataPoint {
  date: string;
  count: number;
}

interface UserProfileInfo {
  created?: string;
}

interface UserStatsResponse {
  heatmap?: HeatmapDataPoint[];
}

@Component({
  components: {
    highcharts: Chart,
  },
})
export default class UserHeatmap extends Vue {
  @Prop() username!: string;

  chart: Highcharts.Chart | null = null;
  heatmapData: HeatmapDataPoint[] = [];
  isLoading = true;
  selectedYear: number = new Date().getFullYear();
  availableYears: number[] = [];
  T = T;
  ui = ui;

  // Define $refs type
  $refs!: {
    heatmapContainer: HTMLElement;
  };

  created(): void {
    if (!this.T) {
      this.T = T;
    }
  }

  async mounted(): Promise<void> {
    this.isLoading = true;
    try {
      const response = await apiCall<{ username: string }, UserStatsResponse>(
        '/api/user/stats/',
      )({ username: this.username });

      this.heatmapData = response.heatmap || [];

      const profileResponse = await apiCall<
        { username: string },
        UserProfileInfo
      >('/api/user/profile/')({ username: this.username });

      const currentYear = new Date().getFullYear();
      const years = new Set<number>();

      if (profileResponse.created) {
        const creationYear = new Date(profileResponse.created).getFullYear();

        for (let year = creationYear; year <= currentYear; year++) {
          years.add(year);
        }
      } else {
        years.add(currentYear);
      }

      this.availableYears = Array.from(years).sort((a, b) => b - a);

      if (this.availableYears.length > 0) {
        this.selectedYear = this.availableYears[0];
      }

      this.renderHeatmap();
    } catch (error) {
      console.error('Failed to load heatmap data', error);
    } finally {
      this.isLoading = false;
    }
  }

  updateHeatmap(): void {
    this.renderHeatmap();
  }

  renderHeatmap(): void {
    if (!this.$refs.heatmapContainer) return;

    const startDate = new Date(this.selectedYear, 0, 1);
    const firstDayOffset = startDate.getDay();

    const dateMap = new Map();
    this.heatmapData.forEach((item) => {
      const date = new Date(item.date);
      if (date.getFullYear() === this.selectedYear) {
        dateMap.set(item.date, item.count);
      }
    });

    const formattedData: Array<[number, number, number]> = [];
    const now = new Date();

    const lastDay = new Date(this.selectedYear, 11, 31);
    const firstDay = new Date(this.selectedYear, 0, 1);
    const totalDays =
      Math.round(
        (lastDay.getTime() - firstDay.getTime()) / (24 * 60 * 60 * 1000),
      ) + 1;
    const totalWeeks = Math.ceil(totalDays / 7);

    for (let i = 0; i < totalDays; i++) {
      const currentDate = new Date(this.selectedYear, 0, i + 1);
      const dateStr = currentDate.toISOString().split('T')[0];
      const weekNumber = Math.floor((i + firstDayOffset) / 7);
      const dayOfWeek = currentDate.getDay();

      if (currentDate > now) {
        formattedData.push([weekNumber, dayOfWeek, 0]);
      } else {
        const count = dateMap.get(dateStr) || 0;
        formattedData.push([weekNumber, dayOfWeek, count]);
      }
    }

    const boxWidth = 11;
    const boxHeight = 16;
    const boxPadding = 2;
    const cellWidth = boxWidth + boxPadding;
    const cellHeight = boxHeight + boxPadding;

    const chartWidth = (totalWeeks + 1) * cellWidth;
    const chartHeight = 7 * cellHeight + 15;

    const options: Highcharts.Options = {
      chart: {
        renderTo: this.$refs.heatmapContainer,
        type: 'heatmap',
        height: chartHeight,
        width: chartWidth,
        spacing: [0, 0, 10, 0],
        margin: [0, 0, 15, 0],
        backgroundColor: 'transparent',
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
          { from: -1, to: 0, color: '#dddddd' },
          { from: 1, to: 4, color: '#739DE3' },
          { from: 5, to: 9, color: '#5588DD' },
          { from: 10, to: 1000, color: '#4670B5' },
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

    // Create chart with properly typed options
    this.chart = new Highcharts.Chart(options);
  }

  beforeDestroy(): void {
    if (this.chart) {
      this.chart.destroy();
    }
  }
}
</script>

<style lang="scss" scoped>
.user-heatmap-container {
  width: 100%;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
}

.user-heatmap-wrapper {
  position: relative;
  width: 100%;
  background-color: white;
  border-radius: 6px;
}

.year-selector {
  position: absolute;
  top: 10px;
  right: 10px;
  z-index: 10;
  width: 120px;
}

.user-heatmap {
  width: 100%;
  min-height: 130px;
  margin: 0;
  padding: 20px 0 0 0; /* Keep top padding for the year selector */
  display: flex;
  justify-content: center;
  align-items: flex-start;
  overflow-x: auto;
}
</style>
