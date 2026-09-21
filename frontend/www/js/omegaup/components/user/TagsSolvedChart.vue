<template>
  <div class="tags-solved-chart">
    <div class="d-flex align-items-center justify-content-between mb-2">
      <h5 class="chart-title mb-0">{{ T.profileTagsSolved }}</h5>
      <div
        v-if="hasAnyData"
        class="btn-group btn-group-sm view-toggle"
        role="group"
        aria-label="Toggle between chart and table"
      >
        <button
          type="button"
          class="btn"
          :class="view === 'chart' ? 'btn-primary' : 'btn-outline-secondary'"
          data-view-toggle="chart"
          @click="setView('chart')"
        >
          {{ T.profileTagsViewChart }}
        </button>
        <button
          type="button"
          class="btn"
          :class="view === 'table' ? 'btn-primary' : 'btn-outline-secondary'"
          data-view-toggle="table"
          @click="setView('table')"
        >
          {{ T.profileTagsViewTable }}
        </button>
      </div>
    </div>
    <div v-if="hasAnyData" :data-active-view="view" class="card-body-container">
      <div v-if="view === 'chart'" class="chart-container">
        <highcharts :options="chartOptions"></highcharts>
      </div>
      <div v-else class="table-container">
        <table class="table table-sm table-striped table-hover tags-table">
          <thead>
            <tr>
              <th
                scope="col"
                class="sortable"
                :class="sortClass('name')"
                @click="toggleSort('name')"
              >
                {{ T.profileTagsColumnName }}
              </th>
              <th
                scope="col"
                class="sortable text-right"
                :class="sortClass('count')"
                @click="toggleSort('count')"
              >
                {{ T.profileTagsColumnCount }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in sortedTagsFull" :key="row.name" data-tag-row>
              <td>{{ row.name }}</td>
              <td class="text-right">{{ row.count }}</td>
            </tr>
          </tbody>
        </table>
      </div>
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

type SortKey = 'name' | 'count';
type SortDirection = 'asc' | 'desc';
type ViewMode = 'chart' | 'table';

@Component({
  components: {
    highcharts: Chart,
  },
})
export default class TagsSolvedChart extends Vue {
  @Prop({ required: true }) tags!: TagStats[];
  @Prop({ default: () => [] }) tagsFull!: TagStats[];

  T = T;

  private view: ViewMode = 'chart';
  private sortKey: SortKey = 'count';
  private sortDirection: SortDirection = 'desc';

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

  get hasAnyData(): boolean {
    return this.tags.length > 0 || this.tagsFull.length > 0;
  }

  get chartData(): Array<{ name: string; y: number; color: string }> {
    return this.tags.map((tag, index) => ({
      name: tag.name,
      y: tag.count,
      color: this.colors[index % this.colors.length],
    }));
  }

  get sortedTagsFull(): TagStats[] {
    const source = this.tagsFull.length > 0 ? this.tagsFull : this.tags;
    const rows = source.slice();
    const direction = this.sortDirection === 'asc' ? 1 : -1;
    rows.sort((a, b) => {
      if (a[this.sortKey] < b[this.sortKey]) return -1 * direction;
      if (a[this.sortKey] > b[this.sortKey]) return 1 * direction;
      return 0;
    });
    return rows;
  }

  setView(view: ViewMode): void {
    this.view = view;
  }

  toggleSort(key: SortKey): void {
    if (this.sortKey === key) {
      this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
      this.sortKey = key;
      this.sortDirection = key === 'name' ? 'asc' : 'desc';
    }
  }

  sortClass(key: SortKey): string {
    if (this.sortKey !== key) return '';
    return this.sortDirection === 'asc' ? 'sort-asc' : 'sort-desc';
  }

  get chartOptions(): Highcharts.Options {
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
          data: this.chartData,
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

.view-toggle .btn {
  font-size: 0.8rem;
}

.table-container {
  max-height: 320px;
  overflow-y: auto;
}

.tags-table {
  margin-bottom: 0;
  font-size: 0.85rem;

  th.sortable {
    cursor: pointer;
    user-select: none;
  }

  th.sort-asc::after {
    content: ' \25B2';
    font-size: 0.7em;
  }

  th.sort-desc::after {
    content: ' \25BC';
    font-size: 0.7em;
  }
}
</style>
