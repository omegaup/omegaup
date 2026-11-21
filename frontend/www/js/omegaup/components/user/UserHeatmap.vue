<template>
  <div class="user-heatmap-container">
    <div class="user-heatmap-wrapper">
      <div class="heatmap-header">
        <div class="heatmap-stats">
          <div class="heatmap-primary">
            <span class="stat-value">{{ totalSubmissions }}</span>
            <span class="stat-label">
              {{
                ui.formatString(T.userHeatmapSubmissionsInYear, {
                  year: selectedYear,
                })
              }}
            </span>
          </div>
          <div class="heatmap-secondary">
            <div class="secondary-item">
              <span class="stat-label"
                >{{ T.userHeatmapTotalActiveDays }}:</span
              >
              <span class="stat-value">{{ activeDays }}</span>
            </div>
            <div v-if="isCurrentYearSelected" class="secondary-item">
              <span class="stat-label">{{ T.userHeatmapCurrentStreak }}:</span>
              <span class="stat-value">{{ currentStreak }}</span>
            </div>
            <div class="secondary-item">
              <span class="stat-label">{{ T.userHeatmapMaxStreak }}:</span>
              <span class="stat-value">{{ maxStreak }}</span>
            </div>
          </div>
        </div>
      </div>
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
import { defineComponent, PropType } from 'vue';
import * as Highcharts from 'highcharts/highstock';
import HighchartsHeatmap from 'highcharts/modules/heatmap';
import T from '../../lang';
import * as ui from '../../ui';
import { getHeatmapChartOptions } from '../../user/profile';

HighchartsHeatmap(Highcharts);

// Define color variables
export const COLORS = {
  emptyCell: 'var(--user-heatmap-empty-cell-color)',
  lowActivity: 'var(--user-heatmap-low-activity-color)',
  mediumActivity: 'var(--user-heatmap-medium-activity-color)',
  highActivity: 'var(--user-heatmap-high-activity-color)',
  background: 'var(--user-heatmap-background-color)',
  wrapper: 'var(--user-heatmap-wrapper-background-color)',
};

export default defineComponent({
  name: 'UserHeatmap',
  props: {
    username: {
      type: String,
      required: true,
    },
    availableYears: {
      type: Array as PropType<number[]>,
      default: () => [],
    },
    isLoading: {
      type: Boolean,
      default: false,
    },
    data: {
      type: Array as PropType<any[]>,
      default: () => [],
    },
  },
  emits: ['year-changed'],
  data() {
    return {
      chart: null as Highcharts.Chart | null,
      selectedYear: new Date().getFullYear(),
      totalSubmissions: 0,
      activeDays: 0,
      maxStreak: 0,
      currentStreak: 0,
      hasRendered: false,
      T,
      ui,
      COLORS,
    };
  },
  computed: {
    isCurrentYearSelected(): boolean {
      return this.selectedYear === new Date().getFullYear();
    },
    heatmapContainer(): HTMLElement {
      return this.$refs.heatmapContainer as HTMLElement;
    },
  },
  watch: {
    availableYears: {
      immediate: true,
      deep: true,
      handler(newValue: number[]): void {
        if (!newValue?.length) return;

        this.selectedYear = newValue[0];
        this.hasRendered = false;

        this.$nextTick(() => {
          if (this.data && this.data.length > 0) {
            this.renderHeatmap();
          }
        });
      },
    },
    data: {
      immediate: true,
      deep: true,
      handler(): void {
        if (!this.data?.length) return;

        this.hasRendered = false;

        this.$nextTick(() => {
          this.renderHeatmap();
        });
      },
    },
    isLoading(newValue: boolean): void {
      if (!newValue && this.data?.length) {
        this.hasRendered = false;
        this.$nextTick(() => {
          this.renderHeatmap();
        });
      }
    },
    selectedYear(): void {
      this.hasRendered = false;
      this.$nextTick(() => {
        if (this.data?.length) {
          this.renderHeatmap();
        }
      });
    },
  },
  mounted(): void {
    this.$nextTick(() => {
      if (this.availableYears?.length) {
        this.selectedYear = this.availableYears[0];
      }

      if (this.data?.length) {
        this.renderHeatmap();
      }
    });
  },
  beforeDestroy(): void {
    if (this.chart) {
      this.chart.destroy();
    }
  },
  methods: {
    onYearChange(): void {
      this.$emit('year-changed', this.selectedYear);
    },

    renderHeatmap(): void {
      if (this.hasRendered) return;

      if (!this.heatmapContainer) {
        return;
      }

      // Use the same stats data that the bar chart uses
      const stats = this.data;

      const startDate = new Date(this.selectedYear, 0, 1);
      const firstDayOffset = startDate.getDay();

      // Create a map for faster lookups, using the same format as bar chart.
      // The map keeps every year so the current streak can cross year
      // boundaries; per-year stats and cells only look up selected-year dates.
      const dateMap = new Map<string, number>();

      if (stats?.length) {
        for (const run of stats) {
          if (!run.date) continue;

          // Only count accepted submissions as activity
          if (run.verdict !== 'AC') continue;

          // Sum runs by date
          const currentCount = dateMap.get(run.date) || 0;
          dateMap.set(run.date, currentCount + run.runs);
        }
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

        // Format date for map lookup (YYYY-MM-DD)
        const dateStr = this.formatDateToString(currentDate);

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

      this.setActivityStats(dateMap, firstDay, now);

      if (this.chart) {
        this.chart.destroy();
      }

      this.chart = new Highcharts.Chart(options);
      this.hasRendered = true;
    },

    formatDateToString(date: Date): string {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    },

    setActivityStats(
      dateMap: Map<string, number>,
      start: Date,
      now: Date,
    ): void {
      const lastDay =
        this.selectedYear === now.getFullYear()
          ? new Date(now.getFullYear(), now.getMonth(), now.getDate())
          : new Date(this.selectedYear, 11, 31);
      let totalSubmissionsCount = 0;
      let activeDays = 0;
      let currentStreak = 0;
      let bestStreak = 0;

      for (
        let currentDate = new Date(start.getTime());
        currentDate <= lastDay;
        currentDate.setDate(currentDate.getDate() + 1)
      ) {
        const dateStr = this.formatDateToString(currentDate);
        const runs = dateMap.get(dateStr) || 0;

        totalSubmissionsCount += runs;

        if (runs > 0) {
          activeDays += 1;
          currentStreak += 1;
          bestStreak = Math.max(bestStreak, currentStreak);
        } else {
          currentStreak = 0;
        }
      }

      this.totalSubmissions = totalSubmissionsCount;
      this.activeDays = activeDays;
      this.maxStreak = bestStreak;
      this.currentStreak = this.computeCurrentStreak(dateMap, lastDay);
    },

    computeCurrentStreak(dateMap: Map<string, number>, lastDay: Date): number {
      const cursor = new Date(lastDay.getTime());

      // A missing submission today should not break the streak yet: fall back
      // to the streak ending yesterday.
      if (!(dateMap.get(this.formatDateToString(cursor)) || 0)) {
        cursor.setDate(cursor.getDate() - 1);
      }

      // Walk backwards without a lower bound so the streak survives year
      // boundaries (e.g. Dec 31 -> Jan 1).
      let streak = 0;
      while ((dateMap.get(this.formatDateToString(cursor)) || 0) > 0) {
        streak += 1;
        cursor.setDate(cursor.getDate() - 1);
      }
      return streak;
    },
  },
});
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

.heatmap-header {
  padding: 12px 14px 4px 14px;
  padding-right: 140px; /* Make room for year selector */
}

.heatmap-stats {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}

.heatmap-primary {
  display: flex;
  align-items: baseline;
  gap: 8px;
}

.heatmap-primary .stat-value {
  font-size: 1.5rem;
  font-weight: 600;
}

.heatmap-primary .stat-label {
  font-size: 0.75rem;
}

.heatmap-secondary {
  display: flex;
  align-items: center;
  gap: 18px;
  flex-wrap: wrap;
}

.secondary-item {
  display: flex;
  align-items: baseline;
  gap: 6px;
}

.secondary-item .stat-label {
  font-size: 0.75rem;
}

.secondary-item .stat-value {
  font-size: 1rem;
  font-weight: 600;
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
