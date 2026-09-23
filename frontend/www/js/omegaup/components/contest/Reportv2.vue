<template>
  <div class="card mb-5">
    <div class="card-header">
      {{ ui.formatString(T.contestReport, { contest_alias: contestAlias }) }}
    </div>
    <div class="card-body">
      <p class="card-text text-end">
        <a :href="`/contest/${contestAlias}/report/print/`">
          <font-awesome-icon icon="print" />
        </a>
      </p>
      <div
        v-for="contestantData in contestReport"
        :key="contestantData.username"
        class="card-text pb-2"
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
            <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th>{{ T.wordsGroup }}</th>
                  <th>{{ T.rankScore }}</th>
                  <th>{{ T.wordsDetails }}</th>
                </tr>
              </thead>
              <tbody>
                <template
                  v-for="(group, groupIndex) in getGroupsByProblemAndUser(
                    item.run_details.details.groups,
                  )"
                >
                  <tr
                    :key="`${contestantData.username}_${item.alias}_${groupIndex}`"
                  >
                    <th>{{ group.group }}</th>
                    <td>{{ group.rankScore }}</td>
                    <td>
                      <button
                        class="btn btn-link"
                        type="button"
                        :title="T.wordsDetails"
                        @click="
                          toggleGroupDetails(
                            contestantData.username,
                            item.alias,
                            groupIndex,
                          )
                        "
                      >
                        <font-awesome-icon
                          v-if="
                            !isGroupExpanded(
                              contestantData.username,
                              item.alias,
                              groupIndex,
                            )
                          "
                          icon="caret-square-down"
                        />
                        <font-awesome-icon v-else icon="caret-square-up" />
                      </button>
                    </td>
                  </tr>
                  <tr
                    v-if="
                      isGroupExpanded(
                        contestantData.username,
                        item.alias,
                        groupIndex,
                      )
                    "
                    :key="`${contestantData.username}_${item.alias}_${groupIndex}_details`"
                  >
                    <td colspan="3">
                      <div class="table-responsive">
                        <table class="table table-striped table-hover">
                          <thead>
                            <tr>
                              <th>{{ T.wordsCase }}</th>
                              <th>{{ T.wordsTimeInSeconds }}</th>
                              <th>{{ T.wordsWallTimeInSeconds }}</th>
                              <th>{{ T.wordsMemoryInMebibytes }}</th>
                              <th>{{ T.wordsStatus }}</th>
                              <th>{{ T.rankScore }}</th>
                              <th>{{ T.wordsDifference }}</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr
                              v-for="detail in group.details"
                              :key="detail.name"
                            >
                              <th>{{ detail.name }}</th>
                              <td>{{ detail.time }}</td>
                              <td>{{ detail.wallTime }}</td>
                              <td>{{ detail.memory }}</td>
                              <td>{{ detail.verdict }}</td>
                              <td>{{ detail.score }}</td>
                              <td>{{ detail.diff }}</td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
        </div>
        <hr />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
library.add(fas);

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
    FontAwesomeIcon,
  },
})
export default class Report extends Vue {
  @Prop() contestReport!: types.ContestReport[];
  @Prop() contestAlias!: string;

  T = T;
  ui = ui;
  expandedGroups: { [key: string]: boolean } = {};

  totalPoints(contestantData: types.ContestReport): string {
    const points = contestantData.total.points ?? 0;
    return ui.formatString(T.contestReportProblemWithPoints, { points });
  }

  groupKey(username: string, alias: string, index: number): string {
    return `${username}_${alias}_${index}`;
  }

  isGroupExpanded(username: string, alias: string, index: number): boolean {
    return !!this.expandedGroups[this.groupKey(username, alias, index)];
  }

  toggleGroupDetails(username: string, alias: string, index: number): void {
    const key = this.groupKey(username, alias, index);
    this.$set(this.expandedGroups, key, !this.expandedGroups[key]);
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
