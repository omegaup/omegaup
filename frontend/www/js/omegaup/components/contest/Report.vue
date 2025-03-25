<template>
  <div class="card">
    <div class="card-header">
      {{ ui.formatString(T.contestReport, { contest_alias: contestAlias }) }}
    </div>
    <div class="card-body">
      <div
        v-for="contestantData in contestReport"
        :key="contestantData.username"
        class="pb-2"
      >
        <h1>
          {{
            ui.formatString(T.contestReportUsername, {
              username: contestantData.username,
            })
          }}
        </h1>
        <h3>
          {{
            ui.formatString(T.contestReportTotalPoints, {
              total_points: getTotalPoints(contestantData.total.points),
            })
          }}
        </h3>
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
          <template v-for="group in getGroups(item)">
            <table
              :key="`${contestantData.username}_${item.alias}_${group.group}_case`"
              class="table table-stripped table-responsive"
            >
              <thead>
                <tr class="text-center">
                  <th scope="col">{{ T.wordsCase }}</th>
                  <th scope="col">{{ T.wordsTimeInSeconds }}</th>
                  <th scope="col">{{ T.wordsWallTimeInSeconds }}</th>
                  <th scope="col">{{ T.wordsMemoryInMebibytes }}</th>
                  <th scope="col">{{ T.wordsStatus }}</th>
                  <th scope="col">{{ T.rankScore }}</th>
                  <th scope="col">{{ T.wordsDifference }}</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="groupCase in group.cases"
                  :key="`${contestantData.username}_${item.alias}_${group.group}_${groupCase.name}_case`"
                >
                  <th scope="row">{{ group.group }}.{{ groupCase.name }}</th>
                  <td class="text-right">
                    {{ groupCase.meta.time.toFixed(3) }}
                  </td>
                  <td class="text-right">
                    {{ groupCase.meta.wall_time.toFixed(3) }}
                  </td>
                  <td class="text-right">
                    {{ groupCase.meta.memory.toFixed(2) }}
                  </td>
                  <td class="text-center">{{ groupCase.verdict }}</td>
                  <td class="text-center">{{ groupCase.score }}</td>
                  <td>
                    <template v-if="groupCase.out_diff">
                      {{ groupCase.out_diff }}
                    </template>
                  </td>
                </tr>
              </tbody>
            </table>

            <table
              :key="`${contestantData.username}_${item.alias}_${group.group}_detail`"
              class="table table-stripped table-responsive pb-2"
            >
              <thead>
                <tr class="text-center">
                  <th scope="col">{{ T.wordsGroup }}</th>
                  <th scope="col">{{ T.rankScore }}</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="groupDetails in item.run_details.details.groups"
                  :key="`${contestantData.username}_${item.alias}_${groupDetails.group}_detail`"
                >
                  <th scope="row">{{ groupDetails.group }}</th>
                  <td class="text-center">{{ groupDetails.score }}</td>
                </tr>
              </tbody>
            </table>
          </template>
        </div>
        <hr />
      </div>
    </div>
    <div class="page-break"></div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';

@Component
export default class Report extends Vue {
  @Prop() contestReport!: types.ContestReport[];
  @Prop() contestAlias!: string;

  T = T;
  ui = ui;

  getTotalPoints(points: null | number): number {
    return points ?? 0;
  }

  getGroups(
    problem: types.ScoreboardRankingProblem,
  ): types.ScoreboardRankingProblemDetailsGroup[] {
    return problem.run_details?.details?.groups ?? [];
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
table {
  page-break-inside: avoid;
  td {
    padding: 0.2em;
  }
}
tr {
  &:nth-child(even) {
    background-color: #eee;
  }
}
@media print {
  @page {
    margin: 1in;
  }
}
</style>
