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
    <div class="float-right">
      <div v-if="showDownloadButton" class="btn-group mr-2">
        <button
          type="button"
          class="btn btn-primary dropdown-toggle scoreboard-download-btn"
          data-toggle="dropdown"
          aria-haspopup="true"
          aria-expanded="false"
        >
          <span class="download-text">{{ T.scoreboardDownload }}</span>
        </button>
        <div class="dropdown-menu">
          <a
            class="dropdown-item"
            href="#"
            @click.prevent="downloadScoreboard(ScoreboardDownloadFormat.Csv)"
            >{{ T.scoreboardDownloadCsv }}</a
          >
          <a
            class="dropdown-item"
            href="#"
            @click.prevent="downloadScoreboard(ScoreboardDownloadFormat.Xlsx)"
            >{{ T.scoreboardDownloadXlsx }}</a
          >
        </div>
      </div>
      <label
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
    </div>
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

export enum ScoreboardDownloadFormat {
  Csv = 'csv',
  Xlsx = 'xlsx',
}

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
  @Prop({ default: false }) showDownloadButton!: boolean;

  T = T;
  ui = ui;
  INF = '∞';
  ScoreboardDownloadFormat = ScoreboardDownloadFormat;
  onlyShowExplicitlyInvited =
    !this.showAllContestants && this.showInvitedUsersFilter;
  nameDisplayOptions: ui.NameDisplayOptions =
    ui.NameDisplayOptions.NameAndUsername;

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

  downloadScoreboard(format: ScoreboardDownloadFormat): void {
    // This will be overridden by parent components to handle the actual download
    this.$emit('download-scoreboard', format);
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

  /* Download button styling */
  .scoreboard-download-btn {
    background-color: var(--arena-button-background-color) !important;
    border-color: var(--arena-button-border-color) !important;
    color: var(--arena-button-text-color) !important;
    font-weight: bold;
    padding: 0.5rem 1rem !important;
    min-width: 100px;
    display: inline-flex !important;
    align-items: center;
    justify-content: space-between;
  }

  .scoreboard-download-btn:hover {
    background-color: var(--arena-button-hover-background-color) !important;
    border-color: var(--arena-button-hover-border-color) !important;
  }

  .download-text {
    color: var(--arena-button-text-color) !important;
    font-weight: bold;
    margin-right: 0.5rem;
    font-size: 14px;
    line-height: 1;
  }

  .dropdown-menu {
    min-width: 140px;
    box-shadow: 0 2px 8px var(--arena-dropdown-menu-shadow);
  }

  .dropdown-item {
    padding: 0.5rem 1rem !important;
    font-size: 0.875rem !important;
    color: var(--arena-dropdown-item-text-color) !important;
    font-weight: 500;
    cursor: pointer;
  }

  .dropdown-item:hover {
    background-color: var(
      --arena-dropdown-item-hover-background-color
    ) !important;
    color: var(--arena-dropdown-item-hover-text-color) !important;
  }
}
</style>
