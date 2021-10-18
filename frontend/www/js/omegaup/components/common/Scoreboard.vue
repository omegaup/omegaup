<template>
  <omegaup-arena
    :active-tab="'ranking'"
    :show-tabs="false"
    :title="name"
    :is-admin="isAdmin"
  >
    <template #socket-status>
      <sup :class="socketClass" :title="socketStatusTitle">{{
        socketStatus
      }}</sup>
    </template>
    <template #clock>
      <div v-if="!deadline" class="clock">{{ INF }}</div>
      <omegaup-countdown
        v-else
        class="clock"
        :target-time="deadline"
      ></omegaup-countdown>
    </template>
    <template #arena-scoreboard>
      <omegaup-arena-scoreboard
        :show-invited-users-filter="false"
        :problems="problems"
        :ranking="ranking"
        :last-updated="lastUpdated"
      ></omegaup-arena-scoreboard>
    </template>
  </omegaup-arena>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import * as ui from '../../ui';
import T from '../../lang';
import arena_Arena from '../arena/Arena.vue';
import arena_Scoreboard from '../arena/Scoreboard.vue';
import omegaup_Countdown from '../Countdown.vue';
import { SocketStatus } from '../../arena/events_socket';

@Component({
  components: {
    'omegaup-arena': arena_Arena,
    'omegaup-arena-scoreboard': arena_Scoreboard,
    'omegaup-countdown': omegaup_Countdown,
  },
})
export default class ProblemsetScoreboard extends Vue {
  @Prop() name!: string;
  @Prop() finishTime!: Date;
  @Prop() ranking!: types.ScoreboardRankingEntry[];
  @Prop() problems!: types.NavbarProblemsetProblem[];
  @Prop() lastUpdated!: Date;
  @Prop({ default: false }) isAdmin!: boolean;
  @Prop({ default: 2 }) digitsAfterDecimalPoint!: number;
  @Prop({ default: SocketStatus.Waiting }) socketStatus!: SocketStatus;
  @Prop({ default: true }) socketConnected!: boolean;

  T = T;
  ui = ui;
  INF = '∞';

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

  get deadline(): null | Date {
    return this.finishTime ?? null;
  }
}
</script>
