<template>
  <div class="omegaup-scoreboard">
    <!-- id-lint off -->
    <div id="ranking-chart"></div><!-- id-lint on -->
    <label><input class="toggle-contestants"
           type="checkbox"
           v-model="onlyShowExplicitlyInvited"> {{ T.scoreboardShowOnlyInvitedIdentities}}</label>
    <table>
      <thead>
        <tr>
          <th><!-- legend --></th>
          <th><!-- position --></th>
          <th>{{ T.wordsUser }}</th>
          <th v-for="(problem, index) in problems">
            <a v-bind:href="'#problems/' + problem.alias"
                v-bind:title="problem.alias">{{ UI.columnName(index) }}</a>
          </th>
          <th v-bind:colspan="2 + problems.length">{{ T.wordsTotal }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-bind:class="user.username"
            v-for="(user, userIndex) in ranking"
            v-if="showUser(user.is_invited)">
          <td class="legend"
              v-bind:style="{ backgroundColor: legendColor(userIndex) }"></td>
          <td class="position">{{ user.place }}</td>
          <td class="user">{{ UI.rankingUsername(user) }} <img alt=""
               height="11"
               v-bind:src="'/media/flags/' + user.country.toLowerCase() + '.png'"
               v-bind:title="user.country"
               v-if="user.country"
               width="16"></td>
          <td v-bind:class="problemClass(problem, problems[problemIndex].alias)"
              v-for="(problem, problemIndex) in user.problems">
            <template v-if="problem.runs &gt; 0">
              <div class="points">
                {{ renderPoints(problem) }}
              </div>
              <div class="penalty">
                <span v-if="showPenalty">{{ problem.penalty }}</span> ({{ problem.runs }})
              </div>
            </template>
            <template v-else="">
              -
            </template>
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
      </tbody>
    </table>
    <div class="footer">
      {{ lastUpdatedString }}
    </div>
  </div>
</template>

<style>
.omegaup-scoreboard {
  max-width: 900px;
  margin: 0 auto;
}
.omegaup-scoreboard a {
  color: #5588DD;
}
.omegaup-scoreboard .footer {
  padding: 1em;
  text-align: right;
  font-size: 70%;
  color: grey;
}

.omegaup-scoreboard table {
  border-collapse: collapse;
  width: 100%;
}
.omegaup-scoreboard th {
  padding: 0.2em;
  text-align: center;
}
.omegaup-scoreboard td {
  text-align: center;
  vertical-align: middle;
  border: 1px solid #000;
  padding: 0.2em;
}
.omegaup-scoreboard td.accepted {
  background: #dfd;
}
.omegaup-scoreboard td.pending {
  background: #ddf;
}
.omegaup-scoreboard td.wrong {
  background: #fdd;
}
.omegaup-scoreboard td.position.recent-event {
  font-weight: bold;
  background: #dfd;
}
.omegaup-scoreboard td.accepted.recent-event {
  background: #8f8;
}
.omegaup-scoreboard td .points {
  font-weight: bold;
}
.omegaup-scoreboard td .penalty {
  font-size: 70%;
}
.omegaup-scoreboard td.position {
  width: 3.5em;
}
.omegaup-scoreboard td.legend {
  width: .5em;
}
.omegaup-scoreboard td[class$='points'] {
  width: 3.5em;
  border-right-style: dotted;
}
.omegaup-scoreboard td[class$='penalty'] {
  border-left-width: 0;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import omegaup from '../../api.js';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';

@Component
export default class ArenaScoreboard extends Vue {
  @Prop() scoreboardColors!: string[];
  @Prop() problems!: omegaup.Problem[];
  @Prop() ranking!: omegaup.ScoreboardUser[];
  @Prop() lastUpdated!: Date;
  @Prop({ default: true }) showPenalty!: boolean;
  @Prop({ default: 2 }) digitsAfterDecimalPoint!: number;

  T = T;
  UI = UI;
  onlyShowExplicitlyInvited = true;

  get lastUpdatedString(): string {
    return !this.lastUpdated ? '' : this.lastUpdated.toString();
  }

  legendColor(idx: number): string {
    return this.scoreboardColors && idx < this.scoreboardColors.length
      ? this.scoreboardColors[idx]
      : '';
  }

  renderPoints(p: omegaup.ScoreboardUserProblem): string {
    return (
      (p.points > 0 ? '+' : '') + p.points.toFixed(this.digitsAfterDecimalPoint)
    );
  }

  totalRuns(u: omegaup.ScoreboardUser): number {
    return u.problems.reduce(
      (acc: number, val: omegaup.ScoreboardUserProblem) => acc + val.runs,
      0,
    );
  }

  problemClass(p: omegaup.ScoreboardUserProblem, alias: string): string {
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
    return userIsInvited || !this.onlyShowExplicitlyInvited;
  }
}

</script>
