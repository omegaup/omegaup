<template>
  <div class="user-heatmap-container">
    <div class="user-heatmap-wrapper">
      <div class="year-selector">
        <select
          v-model="selectedYear"
          class="form-control"
          @change="onYearChange"
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
import { Vue, Component, Prop, Ref, Watch } from 'vue-property-decorator';
import * as Highcharts from 'highcharts/highstock';
import HighchartsHeatmap from 'highcharts/modules/heatmap';
import T from '../../lang';
import * as ui from '../../ui';
import { getHeatmapChartOptions } from '../../user/profile';

HighchartsHeatmap(Highcharts);

interface HeatmapDataPoint {
  date: string;
  count: number;
}

// Define color variables
export const COLORS = {
  emptyCell: 'var(--user-heatmap-empty-cell-color)',
  lowActivity: 'var(--user-heatmap-low-activity-color)',
  mediumActivity: 'var(--user-heatmap-medium-activity-color)',
  highActivity: 'var(--user-heatmap-high-activity-color)',
  background: 'var(--user-heatmap-background-color)',
  wrapper: 'var(--user-heatmap-wrapper-background-color)',
};

@Component({})
export default class UserHeatmap extends Vue {
  @Prop() username!: string;
  @Prop({ default: () => [] }) heatmapData!: HeatmapDataPoint[];
  @Prop({ default: () => [] }) availableYears!: number[];
  @Prop({ default: false }) isLoading!: boolean;

  chart: Highcharts.Chart | null = null;
  selectedYear: number = new Date().getFullYear();
  T = T;
  ui = ui;
  COLORS = COLORS;

  @Ref('heatmapContainer') readonly heatmapContainer!: HTMLElement;

  mounted(): void {
    this.$nextTick(() => {
      if (this.availableYears && this.availableYears.length > 0) {
        this.selectedYear = this.availableYears[0];
      }

      if (this.heatmapData && this.heatmapData.length > 0) {
        this.renderHeatmap();
      }
    });
  }

  @Watch('availableYears', { immediate: true, deep: true })
  onAvailableYearsChange(newValue: number[]): void {
    if (!newValue || !newValue.length) return;

    this.selectedYear = newValue[0];

    this.$nextTick(() => {
      if (this.heatmapData && this.heatmapData.length > 0) {
        this.renderHeatmap();
      }
    });
  }

  @Watch('heatmapData', { immediate: true, deep: true })
  onHeatmapDataChange(newValue: HeatmapDataPoint[]): void {
    if (!newValue || !newValue.length) return;

    this.$nextTick(() => {
      this.renderHeatmap();
    });
  }

  @Watch('isLoading')
  onLoadingChange(newValue: boolean): void {
    if (!newValue && this.heatmapData && this.heatmapData.length > 0) {
      this.$nextTick(() => {
        this.renderHeatmap();
      });
    }
  }

  onYearChange(): void {
    this.$emit('year-changed', this.selectedYear);
  }

  renderHeatmap(): void {
    if (!this.heatmapContainer) {
      return;
    }

    const startDate = new Date(this.selectedYear, 0, 1);
    const firstDayOffset = startDate.getDay();

    const dateMap = new Map<string, number>();
    if (this.heatmapData) {
      this.heatmapData.forEach((item) => {
        if (!item.date) return;

        const dateObj = new Date(item.date);
        const year = dateObj.getUTCFullYear();
        const month = String(dateObj.getUTCMonth() + 1).padStart(2, '0');
        const day = String(dateObj.getUTCDate()).padStart(2, '0');
        const normalizedDate = `${year}-${month}-${day}`;

        if (year === this.selectedYear) {
          dateMap.set(normalizedDate, item.count);
        }
      });
    }

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
      const year = currentDate.getFullYear();
      const month = String(currentDate.getMonth() + 1).padStart(2, '0');
      const day = String(currentDate.getDate()).padStart(2, '0');
      const dateStr = `${year}-${month}-${day}`;

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

    const options = getHeatmapChartOptions({
      heatmapContainer: this.heatmapContainer,
      formattedData,
      startDate,
      firstDayOffset,
      totalWeeks,
      boxWidth,
      boxHeight,
      boxPadding,
      chartWidth,
      chartHeight,
      colors: this.COLORS,
    });

    if (this.chart) {
      this.chart.destroy();
    }

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
  background-color: var(--user-heatmap-wrapper-background-color);
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
