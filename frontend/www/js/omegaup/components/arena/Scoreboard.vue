<template>
  <div class="omegaup-scoreboard px-2">
    <slot name="scoreboard-header">
      <div class="text-center mt-4 pt-2">
        <h2 class="mb-4">
          <span>{{ title }}</span>
          <slot name="socket-status">
            <sup :class="socketClass" :title="socketStatusTitle">{{
              socketStatus
            }}</sup>
          </slot>
        </h2>
        <div v-if="!finishTime" class="clock">{{ INF }}</div>
        <omegaup-countdown
          v-else
          class="clock"
          :target-time="finishTime"
        ></omegaup-countdown>
      </div>
    </slot>
    <highcharts
      v-if="rankingChartOptions && Object.keys(rankingChartOptions).length"
      :options="rankingChartOptions"
    ></highcharts>
    <div v-else class="bg-white text-center p-4 mb-3">
      {{ T.rankingNoUsers }}
    </div>
    <label v-if="showInvitedUsersFilter">
      <input
        v-model="onlyShowExplicitlyInvited"
        class="toggle-contestants"
        type="checkbox"
      />
      {{ T.scoreboardShowOnlyInvitedIdentities }}</label
    >
    <label class="float-right"
      >{{ T.scoreboardShowParticipantsNames }}:
      <select
        v-model="nameDisplayOptions"
        class="form-control"
        data-scoreboard-options
      >
        <option :value="ui.NameDisplayOptions.Name">{{ T.wordsName }}</option>
        <option :value="ui.NameDisplayOptions.Username">
          {{ T.scoreboardAccountName }}
        </option>
        <option :value="ui.NameDisplayOptions.NameAndUsername">
          {{ T.scoreboardNameAndAccountName }}
        </option>
      </select>
    </label>
    <div class="table-responsive">
      <table data-table-scoreboard class="table">
        <thead>
          <tr>
            <th><!-- legend --></th>
            <th><!-- position --></th>
            <th>{{ T.contestParticipant }}</th>
            <th>{{ T.wordsTotal }}</th>
            <th v-for="(problem, index) in problems" :key="problem.alias">
              <a :href="'#problems/' + problem.alias" :title="problem.alias">{{
                ui.columnName(index)
              }}</a>
            </th>
          </tr>
        </thead>
        <tbody>
          <template v-for="(user, userIndex) in ranking">
            <tr
              v-if="showUser(user.is_invited)"
              :key="`${user.username}-${user.virtual}`"
              :class="user.username"
            >
              <td class="legend" :class="legendClass(userIndex)"></td>
              <td class="position" data-table-scoreboard-position>
                {{ user.place || '—' }}
              </td>
              <td class="user" data-table-scoreboard-username>
                {{ ui.rankingUsername(user, nameDisplayOptions) }}
                <img
                  v-if="user.country"
                  alt=""
                  height="11"
                  :src="`/media/flags/${user.country.toLowerCase()}.png`"
                  :title="user.country"
                  width="16"
                />
              </td>
              <td>
                <div class="points">
                  {{ user.total.points.toFixed(digitsAfterDecimalPoint) }}
                </div>
                <div class="penalty">
                  {{ user.total.penalty }} ({{ totalRuns(user) }})
                </div>
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
            </tr>
          </template>
        </tbody>
      </table>
    </div>
    <div class="table-responsive mt-4">
      <table class="table">
        <thead>
          <tr>
           <th>Submission ID</th>
            <th>Status</th>
            <th>Details</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="submission in submissions" :key="submission.id">
            <td>{{ submission.id }}</td>
            <td>
              <span :title="getVerdictTooltip(submission.verdict)">
                {{ submission.verdict }}
              </span>
            </td>
            <td>{{ submission.details }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="footer">
      {{ lastUpdatedString }}
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';

import * as Highcharts from 'highcharts/highstock';
import { Chart } from 'highcharts-vue';
import { types } from '../../api_types';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import * as ui from '../../ui';
import * as time from '../../time';
import omegaup_Countdown from '../Countdown.vue';
import { SocketStatus } from '../../arena/events_socket';

@Component({
  components: {
    highcharts: Chart,
    'omegaup-countdown': omegaup_Countdown,
  },
})
export default class ArenaScoreboard extends Vue {
  @Prop({ default: 10 }) numberOfPositions!: number;
  @Prop() problems!: omegaup.Problem[];
  @Prop() ranking!: types.ScoreboardRankingEntry[];
  @Prop({ default: null }) rankingChartOptions!: Highcharts.Options | null;
  @Prop() lastUpdated!: Date;
  @Prop({ default: true }) showInvitedUsersFilter!: boolean;
  @Prop({ default: true }) showPenalty!: boolean;
  @Prop({ default: false }) showAllContestants!: boolean;
  @Prop({ default: 2 }) digitsAfterDecimalPoint!: number;
  @Prop() title!: string;
  @Prop({ default: null }) finishTime!: null | Date;
  @Prop({ default: SocketStatus.Waiting }) socketStatus!: SocketStatus;

  T = T;
  ui = ui;
  INF = '∞';
  onlyShowExplicitlyInvited =
    !this.showAllContestants && this.showInvitedUsersFilter;
  nameDisplayOptions: ui.NameDisplayOptions =
    ui.NameDisplayOptions.NameAndUsername;

  submissions = [
    { id: 12345, verdict: 'verdictTLE', details: 'Time limit exceeded' },
    { id: 12346, verdict: 'verdictWA', details: 'Wrong answer on test case 3' },
    { id: 12347, verdict: 'verdictRE', details: 'Runtime error in submission' },
    // More submissions can be added here.
  ];

  get lastUpdatedString(): null | string {
    if (!this.lastUpdated) return null;
    return ui.formatString(T.scoreboardLastUpdated, {
      datetime: time.formatDateTime(this.lastUpdated),
    });
  }

  get socketClass(): string {
    if (this.socketStatus === SocketStatus.Connected) {
      return 'socket-status socket-status-ok';
    }
    if (this.socketStatus === SocketStatus.Failed) {
      return 'socket-status socket-status-error';
    }
    return 'socket-status';
  }

  get socketStatusTitle(): string {
    if (this.socketStatus === SocketStatus.Connected) {
      return T.socketStatusConnected;
    }
    if (this.socketStatus === SocketStatus.Failed) {
      return T.socketStatusFailed;
    }
    return T.socketStatusWaiting;
  }

  legendClass(idx: number): string {
    return idx < this.numberOfPositions ? `legend-${idx + 1}` : '';
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
getVerdictTooltip(verdict: string): string {
    const tooltips = {
      verdictJE: this.$t('verdictJE_tooltip'),
      verdictML: this.$t('verdictML_tooltip'),
      verdictMLE: this.$t('verdictMLE_tooltip'),
      verdictOL: this.$t('verdictOL_tooltip'),
      verdictOLE: this.$t('verdictOLE_tooltip'),
      verdictPA: this.$t('verdictPA_tooltip'),
      verdictRE: this.$t('verdictRE_tooltip'),
      verdictRFE: this.$t('verdictRFE_tooltip'),
      verdictRTE: this.$t('verdictRTE_tooltip'),
      verdictTLE: this.$t('verdictTLE_tooltip'),
      verdictTO: this.$t('verdictTO_tooltip'),
      verdictVE: this.$t('verdictVE_tooltip'),
      verdictWA: this.$t('verdictWA_tooltip'),
    };
    return tooltips[verdict] || 'No description available.';  
}
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
.omegaup-scoreboard {
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
    border: none;
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

  .user {
    text-wrap: balance;
    overflow-wrap: break-word;
    max-width: 200px;
  }
verdictTLE {
  color: #f44336; 
}

.verdictWA {
  color: #ff9800; 
}

.verdictRE {
  color: #2196f3; 
}
span[title] {
  position: relative;
  cursor: help;
  text-decoration: underline dotted;
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

  .legend-1 {
    background-color: var(--arena-scoreboard-legend-1-background-color);
  }

  .legend-2 {
    background-color: var(--arena-scoreboard-legend-2-background-color);
  }

  .legend-3 {
    background-color: var(--arena-scoreboard-legend-3-background-color);
  }

  .legend-4 {
    background-color: var(--arena-scoreboard-legend-4-background-color);
  }

  .legend-5 {
    background-color: var(--arena-scoreboard-legend-5-background-color);
  }

  .legend-6 {
    background-color: var(--arena-scoreboard-legend-6-background-color);
  }

  .legend-7 {
    background-color: var(--arena-scoreboard-legend-7-background-color);
  }

  .legend-8 {
    background-color: var(--arena-scoreboard-legend-8-background-color);
  }

  .legend-9 {
    background-color: var(--arena-scoreboard-legend-9-background-color);
  }

  .legend-10 {
    background-color: var(--arena-scoreboard-legend-10-background-color);
  }

  .legend {
    width: 0.5em;
    opacity: 0.8;
  }

  .socket-status-error {
    color: var(--arena-socket-status-error-color);
  }

  .socket-status-ok {
    color: var(--arena-socket-status-ok-color);
  }

  .socket-status {
    cursor: help;
  }

  .clock {
    font-size: 3em;
    line-height: 0.4em;
  }
}
</style>
