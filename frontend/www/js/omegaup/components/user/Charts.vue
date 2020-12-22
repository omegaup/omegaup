<template>
  <div class="panel-body">
    <label
      ><input v-model="type" type="radio" value="delta" />
      {{ T.profileStatisticsDelta }}</label
    >
    <label
      ><input v-model="type" type="radio" value="cumulative" />
      {{ T.profileStatisticsCumulative }}</label
    >
    <label
      ><input v-model="type" type="radio" value="total" />
      {{ T.profileStatisticsTotal }}</label
    >
    <div v-if="type != 'total' && type != ''" class="period-group text-center">
      <label
        ><input v-model="period" name="period" type="radio" value="day" />
        {{ T.profileStatisticsDay }}</label
      >
      <label
        ><input v-model="period" name="period" type="radio" value="week" />
        {{ T.profileStatisticsWeek }}</label
      >
      <label
        ><input v-model="period" name="period" type="radio" value="month" />
        {{ T.profileStatisticsMonth }}</label
      >
      <label
        ><input v-model="period" name="period" type="radio" value="year" />
        {{ T.profileStatisticsYear }}</label
      >
    </div>
    <highcharts
      v-if="type !== 'total'"
      :options="periodStatisticOptions"
      :update-args="updateArgs"
    ></highcharts>
    <highcharts
      v-else
      :options="aggregateStatisticOptions"
      :update-args="updateArgs"
    ></highcharts>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { Chart } from 'highcharts-vue';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import * as ui from '../../ui';

interface Data {
  runs: omegaup.RunInfo[];
}

interface GroupedPeriods {
  day: omegaup.VerdictByDate;
  week: omegaup.VerdictByDate;
  month: omegaup.VerdictByDate;
  year: omegaup.VerdictByDate;
}

interface GroupedVerdicts {
  day: omegaup.Verdict;
  week: omegaup.Verdict;
  month: omegaup.Verdict;
  year: omegaup.Verdict;
}

interface NormalizedPeriodRunCounts {
  day: omegaup.RunCounts;
  week: omegaup.RunCounts;
  month: omegaup.RunCounts;
  year: omegaup.RunCounts;
}

interface NormalizedRunCounts {
  name: string;
  y: number;
  sliced?: boolean;
  selected?: boolean;
}

const emptyGroupedPeriods = {
  day: { WA: 0, PA: 0, AC: 0, TLE: 0, RTE: 0 },
  week: { WA: 0, PA: 0, AC: 0, TLE: 0, RTE: 0 },
  month: { WA: 0, PA: 0, AC: 0, TLE: 0, RTE: 0 },
  year: { WA: 0, PA: 0, AC: 0, TLE: 0, RTE: 0 },
};

const emptyPeriodRunCount = {
  day: {
    categories: [],
    cumulative: [],
    delta: [],
  },
  week: {
    categories: [],
    cumulative: [],
    delta: [],
  },
  month: {
    categories: [],
    cumulative: [],
    delta: [],
  },
  year: {
    categories: [],
    cumulative: [],
    delta: [],
  },
};

@Component({
  components: {
    highcharts: Chart,
  },
})
export default class UserCharts extends Vue {
  @Prop() data!: Data;
  @Prop() username!: string;
  @Prop() periodStatisticOptions!: Chart;
  @Prop() aggregateStatisticOptions!: Chart;

  T = T;
  ui = ui;
  type = 'delta';
  period: 'day' | 'week' | 'month' | 'year' = 'day';
  updateArgs = [true, true, { duration: 500 }];

  @Watch('type')
  onTypeChanged(newValue: string): void {
    if (newValue === 'total') {
      this.onRenderAggregateStatistics();
    } else {
      this.onRenderPeriodStatistics();
    }
  }

  @Watch('period')
  onPeriodChanged(): void {
    this.onRenderPeriodStatistics();
  }

  mounted(): void {
    this.onRenderPeriodStatistics();
  }

  get totalRuns(): number {
    let total = 0;
    for (const runs of this.data.runs) {
      total += runs.runs;
    }
    return total;
  }

  get normalizedRunCounts(): NormalizedRunCounts[] {
    const stats = this.data.runs;
    const runs = stats.reduce(
      (total: omegaup.Run, amount: omegaup.RunInfo) => {
        total[amount.verdict] += amount.runs;
        return total;
      },
      {
        WA: 0,
        PA: 0,
        AC: 0,
        TLE: 0,
        MLE: 0,
        OLE: 0,
        RTE: 0,
        CE: 0,
        JE: 0,
        VE: 0,
      },
    );
    const verdicts = Object.keys(runs);
    const response: NormalizedRunCounts[] = [];
    for (const verdict of verdicts) {
      const numRuns = runs[verdict];
      if (verdict == 'AC') {
        response.push({
          name: verdict,
          y: numRuns,
          sliced: true,
          selected: true,
        });
      } else {
        response.push({ name: verdict, y: numRuns });
      }
    }
    return response;
  }

  get normalizedPeriodRunCounts(): NormalizedPeriodRunCounts {
    const runs: GroupedPeriods = this.groupedPeriods;
    const periods: Array<'day' | 'week' | 'month' | 'year'> = [
      'day',
      'week',
      'month',
      'year',
    ];
    const response: NormalizedPeriodRunCounts = emptyPeriodRunCount;
    const runsByVerdict: GroupedVerdicts = emptyGroupedPeriods;
    for (const period of periods) {
      response[period] = {
        categories: Object.keys(runs[period]),
        delta: [],
        cumulative: [],
      };
      const verdicts = ['AC', 'PA', 'WA', 'TLE', 'RTE'];
      for (const verdict of verdicts) {
        runsByVerdict[period][verdict] = 0;
      }
      for (const [index, verdict] of verdicts.entries()) {
        response[period].delta[index] = { name: verdict, data: [] };
        response[period].cumulative[index] = {
          name: verdict,
          data: [],
        };
        for (const [ind, date] of response[period].categories.entries()) {
          runsByVerdict[period][verdict] += runs[period][date][verdict];
          response[period].delta[index].data[ind] = runs[period][date][verdict];
          response[period].cumulative[index].data[ind] =
            runsByVerdict[period][verdict];
        }
      }
    }
    return response;
  }

  get groupedPeriods(): GroupedPeriods {
    const stats = this.data.runs;
    const periods: Array<'day' | 'week' | 'month' | 'year'> = [
      'day',
      'week',
      'month',
      'year',
    ];
    for (const [index, run] of stats.entries()) {
      const date = new Date(run.date);
      const day = date.getDay();
      // group by days
      stats[index].day = date.toLocaleDateString(T.locale);
      // group by weeks
      const diffMonday = date.getDate() - day + (day == 0 ? -6 : 1);
      const diffSunday = date.getDate() + (7 - day);
      const firstDay = new Date(date.setDate(diffMonday));
      const lastDay = new Date(date.setDate(diffSunday));
      stats[index].week =
        firstDay.toLocaleDateString(T.locale) +
        ' - ' +
        lastDay.toLocaleDateString(T.locale);
      // group by month
      stats[index].month = run.date.substring(0, 7);
      // group by year
      stats[index].year = run.date.substring(0, 4);
    }
    const periodStats: GroupedPeriods = {
      day: {},
      week: {},
      month: {},
      year: {},
    };
    for (const period of periods) {
      periodStats[period] = stats.reduce(
        (groups: omegaup.VerdictByDate, item: omegaup.RunInfo) => {
          const val = item[period] || '';
          groups[val] = groups[val] || { WA: 0, PA: 0, AC: 0, TLE: 0, RTE: 0 };
          groups[val][item.verdict] += item.runs;
          return groups;
        },
        {},
      );
    }
    return periodStats;
  }

  get normalizedRunCountsForPeriod(): omegaup.RunCounts {
    return this.normalizedPeriodRunCounts[this.period];
  }

  onRenderPeriodStatistics(): void {
    const runs: omegaup.RunCounts = this.normalizedRunCountsForPeriod;
    const data = this.type === 'delta' ? runs.delta : runs.cumulative;
    this.$emit('emit-update-period-statistics', this, runs.categories, data);
  }

  onRenderAggregateStatistics(): void {
    this.$emit('emit-update-aggregate-statistics', this);
  }
}
</script>
