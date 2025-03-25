<template>
  <b-card
    class="mb-5"
    :header="ui.formatString(T.contestReport, { contest_alias: contestAlias })"
  >
    <b-card-text class="text-right">
      <a :href="`/contest/${contestAlias}/report/print/`">
        <b-icon-printer></b-icon-printer>
      </a>
    </b-card-text>
    <b-card-text
      v-for="contestantData in contestReport"
      :key="contestantData.username"
      class="pb-2"
    >
      <h1 class="text-center">
        {{
          ui.formatString(T.contestReportUsername, {
            username: contestantData.username,
          })
        }}
      </h1>
      <h3 class="text-center">{{ totalPoints(contestantData) }}</h3>
      <div
        v-for="item in contestantData.problems"
        :key="`${contestantData.username}_${item.alias}`"
        class="pb-2"
      >
        <h3>
          {{
            ui.formatString(T.contestReportProblemWithAlias, {
              alias: item.alias,
            })
          }}
        </h3>
        <h3>
          {{
            ui.formatString(T.contestReportProblemWithPoints, {
              points: item.points,
            })
          }}
        </h3>
        <h3>{{ T.wordsPoints }}: {{ item.points }}</h3>
        <div v-if="item.run_details">
          <b-table
            striped
            hover
            :items="getGroupsByProblemAndUser(item.run_details.details.groups)"
            :fields="groupColumns"
          >
            <template #cell(details)="row">
              <b-button
                :title="T.wordsDetails"
                variant="link"
                @click="row.toggleDetails"
              >
                <b-icon-caret-down-square
                  v-if="row.detailsShowing"
                ></b-icon-caret-down-square>
                <b-icon-caret-up-square v-else></b-icon-caret-up-square>
              </b-button>
            </template>
            <template #row-details="row">
              <b-table
                responsive
                striped
                hover
                :items="row.item.details"
                :fields="casesColumns"
              ></b-table>
            </template>
          </b-table>
        </div>
      </div>
      <hr />
    </b-card-text>
  </b-card>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';

// Import Bootstrap an BootstrapVue CSS files (order is important)
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';

// Import Only Required Plugins
import {
  TablePlugin,
  ButtonPlugin,
  CardPlugin,
  BIconCaretDownSquare,
  BIconCaretUpSquare,
  BIconPrinter,
} from 'bootstrap-vue';
Vue.use(TablePlugin);
Vue.use(ButtonPlugin);
Vue.use(CardPlugin);

interface GroupDetails {
  name: string;
  time: string;
  wallTime: string;
  memory: string;
  verdict: string;
  score: number;
  diff?: string;
}

@Component({
  components: {
    BIconCaretDownSquare,
    BIconCaretUpSquare,
    BIconPrinter,
  },
})
export default class Report extends Vue {
  @Prop() contestReport!: types.ContestReport[];
  @Prop() contestAlias!: string;

  T = T;
  ui = ui;
  groupColumns = [
    { key: 'group', label: T.wordsGroup, isRowHeader: true },
    { key: 'rank_score', label: T.rankScore },
    { key: 'details', label: T.wordsDetails },
  ];

  casesColumns = [
    { key: 'name', label: T.wordsCase, isRowHeader: true },
    { key: 'time', label: T.wordsTimeInSeconds },
    { key: 'wall_time', label: T.wordsWallTimeInSeconds },
    { key: 'memory', label: T.wordsMemoryInMebibytes },
    { key: 'verdict', label: T.wordsStatus },
    { key: 'score', label: T.rankScore },
    { key: 'diff', label: T.wordsDifference },
  ];

  totalPoints(contestantData: types.ContestReport): string {
    const points = contestantData.total.points ?? 0;
    return ui.formatString(T.contestReportProblemWithPoints, { points });
  }

  getGroupsByProblemAndUser(
    groups: types.RunDetailsGroup[],
  ): { group: string; rankScore: number; details: GroupDetails[] }[] {
    return groups.map((item) => ({
      group: item.group,
      rankScore: item.score,
      details: item.cases.map((row) => ({
        name: row.name,
        time: row.meta.time.toFixed(3),
        wallTime: row.meta.wall_time.toFixed(3),
        memory: row.meta.memory.toFixed(2),
        verdict: row.verdict,
        score: row.score,
        diff: row.out_diff,
      })),
    }));
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

h1 {
  font-size: 1.5em;
  page-break-after: avoid;
  page-break-inside: avoid;
}
h3 {
  font-size: 1.15em;
  page-break-after: avoid;
  page-break-inside: avoid;
}
</style>
