<template>
  <div class="omegaup-scoreboard">
    <!-- id-lint off -->
    <div id="ranking-chart"></div>
    <!-- id-lint on -->
    <label v-if="showInvitedUsersFilter">
      <input
        v-model="onlyShowExplicitlyInvited"
        class="toggle-contestants"
        type="checkbox"
      />
      {{ T.scoreboardShowOnlyInvitedIdentities }}</label
    >
    <table>
      <thead>
        <tr>
          <th><!-- legend --></th>
          <th><!-- position --></th>
          <th>{{ T.wordsUser }}</th>
          <th v-for="(problem, index) in problems" :key="problem.alias">
            <a :href="'#problems/' + problem.alias" :title="problem.alias">{{
              ui.columnName(index)
            }}</a>
          </th>
          <th :colspan="2 + problems.length">{{ T.wordsTotal }}</th>
        </tr>
      </thead>
      <tbody>
        <template v-for="(user, userIndex) in ranking">
          <tr
            v-if="showUser(user.is_invited)"
            :key="user.username"
            :class="user.username"
          >
            <td
              class="legend"
              :style="{ backgroundColor: legendColor(userIndex) }"
            ></td>
            <td class="position">{{ user.place || '—' }}</td>
            <td class="user">
              {{ ui.rankingUsername(user) }}
              <img
                v-if="user.country"
                alt=""
                height="11"
                :src="`/media/flags/${user.country.toLowerCase()}.png`"
                :title="user.country"
                width="16"
              />
            </td>

            <td
              v-for="(problem, problemIndex) in user.problems"
              :key="problem.alias"
              :class="problemClass(problem, problems[problemIndex].alias)"
            >
              <template v-if="problem.runs > 0">
                <div class="points">
                  {{ renderPoints(problem) }}
                </div>
                <div class="penalty">
                  <span v-if="showPenalty">{{ problem.penalty }}</span> ({{
                    problem.runs
                  }})
                </div>
              </template>
              <template v-else> - </template>
            </td>
            <td>
              <div class="points">
                {{ user.total.points.toFixed(digitsAfterDecimalPoint) }}
              </div>
              <div class="penalty">
                {{ user.total.penalty }} ({{ totalRuns(user) }})
              </div>
            </td>
          </tr>
        </template>
      </tbody>
    </table>
    <div class="footer">
      {{ lastUpdatedString }}
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';

import { types } from '../../api_types';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import * as ui from '../../ui';

export const defaultScoreboardColors = [
  '#FB3F51',
  '#FF5D40',
  '#FFA240',
  '#FFC740',
  '#59EA3A',
  '#37DD6F',
  '#34D0BA',
  '#3AAACF',
  '#8144D6',
  '#CD35D3',
];

@Component
export default class ArenaScoreboard extends Vue {
  @Prop({ default: () => defaultScoreboardColors }) scoreboardColors!: string[];
  @Prop() problems!: omegaup.Problem[];
  @Prop() ranking!: types.ScoreboardRankingEntry[];
  @Prop() lastUpdated!: Date;
  @Prop({ default: true }) showInvitedUsersFilter!: boolean;
  @Prop({ default: true }) showPenalty!: boolean;
  @Prop({ default: 2 }) digitsAfterDecimalPoint!: number;

  T = T;
  ui = ui;
  onlyShowExplicitlyInvited = true;

  get lastUpdatedString(): string {
    return !this.lastUpdated ? '' : this.lastUpdated.toString();
  }

  legendColor(idx: number): string {
    return this.scoreboardColors && idx < this.scoreboardColors.length
      ? this.scoreboardColors[idx]
      : '';
  }

  renderPoints(p: types.ScoreboardRankingProblem): string {
    return (
      (p.points > 0 ? '+' : '') + p.points.toFixed(this.digitsAfterDecimalPoint)
    );
  }

  totalRuns(u: types.ScoreboardRankingEntry): number {
    return u.problems.reduce(
      (acc: number, val: types.ScoreboardRankingProblem) => acc + val.runs,
      0,
    );
  }

  problemClass(p: types.ScoreboardRankingProblem, alias: string): string {
    if (p.percent === 100) {
      return `${alias} accepted`;
    } else if (p.pending) {
      return `${alias} pending`;
    } else if (p.percent === 0 && p.runs > 0) {
      return `${alias} wrong`;
    } else {
      return alias;
    }
  }

  showUser(userIsInvited: boolean): boolean {
    // Invited users filter is only available in contests, in a course all users
    // are visible in scoreboard.
    if (!this.showInvitedUsersFilter) return true;
    return userIsInvited || !this.onlyShowExplicitlyInvited;
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
.omegaup-scoreboard {
  max-width: 900px;
  margin: 0 auto;
  a {
    color: var(--arena-scoreboard-a-font-color);
  }
  .footer {
    padding: 1em;
    text-align: right;
    font-size: 70%;
    color: var(--arena-scoreboard-footer-font-color);
  }
  table {
    border-collapse: collapse;
    width: 100%;
  }
  th {
    padding: 0.2em;
    text-align: center;
  }
  td {
    text-align: center;
    vertical-align: middle;
    border: 1px solid var(--arena-scoreboard-td-border-color);
    padding: 0.2em;
    .points {
      font-weight: bold;
    }
    .penalty {
      font-size: 70%;
    }
  }
  .accepted {
    background: var(--arena-scoreboard-accepted-background-color);
  }
  .pending {
    background: var(--arena-scoreboard-pending-background-color);
  }
  .wrong {
    background: var(--arena-scoreboard-wrong-background-color);
  }
  .position.recent-event {
    font-weight: bold;
    background: var(--arena-scoreboard-position-recent-event-background-color);
  }
  .accepted.recent-event {
    background: var(--arena-scoreboard-accepted-recent-event-background-color);
  }
  .position {
    width: 3.5em;
  }
  .legend {
    width: 0.5em;
  }
}
</style>
