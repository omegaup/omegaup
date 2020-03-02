<template>
  <div class="panel-body">
    <label
      ><input type="radio" v-model="type" value="delta" />
      {{ T.profileStatisticsDelta }}</label
    >
    <label
      ><input type="radio" v-model="type" value="cumulative" />
      {{ T.profileStatisticsCumulative }}</label
    >
    <label
      ><input type="radio" v-model="type" value="total" />
      {{ T.profileStatisticsTotal }}</label
    >
    <div
      class="period-group text-center"
      v-if="type != 'total' &amp;&amp; type != ''"
    >
      <label
        ><input name="period" type="radio" v-model="period" value="day" />
        {{ T.profileStatisticsDay }}</label
      >
      <label
        ><input name="period" type="radio" v-model="period" value="week" />
        {{ T.profileStatisticsWeek }}</label
      >
      <label
        ><input name="period" type="radio" v-model="period" value="month" />
        {{ T.profileStatisticsMonth }}</label
      >
      <label
        ><input name="period" type="radio" v-model="period" value="year" />
        {{ T.profileStatisticsYear }}</label
      >
    </div>
    <!-- id-lint off -->
    <div id="verdict-chart"></div>
    <!-- id-lint on -->
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import * as Highcharts from 'highcharts';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';
import omegaup from '../../api.js';

interface Data {
  runs: omegaup.RunInfo[];
}

interface GroupedPeriods {
  [period: string]: omegaup.VerdictByDate;
}

interface GroupedVerdicts {
  [period: string]: omegaup.Verdict;
}

interface NormalizedPeriodRunCounts {
  [period: string]: omegaup.RunCounts;
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

@Component
export default class UserCharts extends Vue {
  @Prop() data!: Data;
  @Prop() username!: string;

  T = T;
  UI = UI;
  type = 'delta';
  period = 'day';
  chart: any = null;

  @Watch('type')
  onTypeChanged(newValue: string): void {
    if (newValue == 'total') {
      this.renderAggregateStatistics();
    } else {
      this.renderPeriodStatistics();
    }
  }

  @Watch('period')
  onPeriodChanged(newValue: string): void {
    this.renderPeriodStatistics();
  }

  mounted(): void {
    this.chart = Highcharts.chart('verdict-chart', {
      title: {
        text: this.UI.formatString(this.T.profileStatisticsVerdictsOf, {
          user: this.username,
        }),
      },
    });
    this.renderPeriodStatistics();
  }

  get totalRuns(): number {
    let total = 0;
    for (const runs of this.data.runs) {
      total += runs['runs'];
    }
    return total;
  }

  get normalizedRunCounts(): NormalizedRunCounts[] {
    const total = this.totalRuns;
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
    const periods = Object.keys(runs);
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
        response[period].cumulative[index] = { name: verdict, data: [] };
        for (const [ind, date] of response[period].categories.entries()) {
          runsByVerdict[period][verdict] += runs[period][date][verdict];
          response[period].delta[index]['data'][ind] =
            runs[period][date][verdict];
          response[period].cumulative[index]['data'][ind] =
            runsByVerdict[period][verdict];
        }
      }
    }
    return response;
  }

  get groupedPeriods(): GroupedPeriods {
    const stats = this.data.runs;
    const periods = ['day', 'week', 'month', 'year'];
    for (const [index, run] of stats.entries()) {
      const date = new Date(run.date);
      const day = date.getDay();
      // group by days
      stats[index]['day'] = date.toLocaleDateString(T.locale);
      // group by weeks
      const diffMonday = date.getDate() - day + (day == 0 ? -6 : 1);
      const diffSunday = date.getDate() + (7 - day);
      const firstDay = new Date(date.setDate(diffMonday));
      const lastDay = new Date(date.setDate(diffSunday));
      stats[index]['week'] =
        firstDay.toLocaleDateString(T.locale) +
        ' - ' +
        lastDay.toLocaleDateString(T.locale);
      // group by month
      stats[index]['month'] = run.date.substring(0, 7);
      // group by year
      stats[index]['year'] = run.date.substring(0, 4);
    }
    const periodStats: GroupedPeriods = {};
    for (const period of periods) {
      periodStats[period] = stats.reduce(
        (groups: GroupedVerdicts, item: omegaup.RunInfo) => {
          const val = item[period];
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

  renderPeriodStatistics(): void {
    const runs: omegaup.RunCounts = this.normalizedRunCountsForPeriod;
    const data = this.type === 'delta' ? runs.delta : runs.cumulative;
    this.chart.update({
      chart: { type: 'column' },
      xAxis: {
        categories: runs.categories,
        title: { text: this.T.profileStatisticsPeriod },
        labels: {
          rotation: -45,
        },
      },
      yAxis: {
        min: 0,
        title: { text: this.T.profileStatisticsNumberOfSolvedProblems },
        stackLabels: {
          enabled: false,
          style: {
            fontWeight: 'bold',
            color: 'gray',
          },
        },
      },
      legend: {
        align: 'right',
        x: -30,
        verticalAlign: 'top',
        y: 25,
        floating: true,
        backgroundColor: 'white',
        borderColor: '#CCC',
        borderWidth: 1,
        shadow: false,
      },
      tooltip: {
        headerFormat: '<b>{point.x}</b><br/>',
        pointFormat: '{series.name}: {point.y}<br/>Total: {point.stackTotal}',
      },
      plotOptions: {
        column: {
          stacking: 'normal',
          dataLabels: {
            enabled: false,
            color: 'white',
          },
        },
      },
      series: [],
    });
    // Removing old series
    while (this.chart.series.length) this.chart.series[0].remove(false);
    // Adding new series
    const numSeries = data.length;
    for (let i = 0; i < numSeries; i++) {
      this.chart.addSeries(data[i]);
    }
    this.chart.redraw();
  }

  renderAggregateStatistics(): void {
    const runs = this.normalizedRunCounts;
    // Removing all series, except last one, because here is where the data
    // will be placed. Otherwise, the chart will not be shown
    while (this.chart.series.length > 1) this.chart.series[0].remove(false);
    this.chart.update({
      chart: {
        plotBackgroundColor: null,
        plotBorderWidth: null,
        plotShadow: false,
        type: 'pie',
      },
      xAxis: {
        title: { text: '' },
      },
      yAxis: {
        title: { text: '' },
      },
      title: {
        text: this.UI.formatString(this.T.profileStatisticsVerdictsOf, {
          user: this.username,
        }),
      },
      tooltip: { pointFormat: '{series.name}: {point.y}' },
      plotOptions: {
        pie: {
          allowPointSelect: true,
          cursor: 'pointer',
          dataLabels: {
            enabled: true,
            color: '#000000',
            connectorColor: '#000000',
            format: '<b>{point.name}</b>: {point.percentage:.1f} % ({point.y})',
          },
        },
      },
      series: [
        {
          name: this.T.profileStatisticsRuns,
          data: runs,
        },
      ],
    });
    this.chart.redraw();
  }
}
</script>
