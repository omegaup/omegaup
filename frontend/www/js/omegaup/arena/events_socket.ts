import * as time from '../time';
import * as ui from '../ui';
import * as api from '../api';
import {
  ContestClarificationType,
  refreshContestClarifications,
} from './clarifications';
import clarificationStore from './clarificationsStore';
import { onRankingChanged, onRankingEvents } from './ranking';
import { updateRun } from './submissions';

export enum SocketStatus {
  Waiting = '↻',
  Failed = '✗',
  Ok = '•',
}

export class EventsSocket {
  uri: string = '';
  shouldRetry: boolean = false;
  socket: WebSocket | null = null;
  socketKeepalive: ReturnType<typeof setTimeout> | null = null;
  retries: number = 10;

  disableSockets: boolean = false;
  problemsetAlias: string | null = null;
  locationProtocol: null | string = null;
  locationHost: null | string = null;
  problemsetId: null | number = null;
  scoreboardToken: null | string = null;
  socketStatus: SocketStatus = SocketStatus.Waiting;
  clarificationInterval: ReturnType<typeof setTimeout> | null = null;
  rankingInterval: ReturnType<typeof setTimeout> | null = null;
  clarificationsOffset: number = 0;
  clarificationsRowcount: number = 20;

  constructor(options?: {
    disableSockets: boolean;
    problemsetAlias: string;
    locationProtocol: string;
    locationHost: string;
    problemsetId: number;
    scoreboardToken: string;
    clarificationsOffset: number;
    clarificationsRowcount: number;
  }) {
    this.socket = null;

    if (options) {
      this.disableSockets = options.disableSockets;
      this.problemsetAlias = options.problemsetAlias;
      this.locationProtocol = options.locationProtocol;
      this.locationHost = options.locationHost;
      this.problemsetId = options.problemsetId;
      this.scoreboardToken = options.scoreboardToken;
      this.clarificationsOffset = options.clarificationsOffset;
      this.clarificationsRowcount = options.clarificationsRowcount;
    }
  }

  connect(): Promise<void> {
    this.shouldRetry = false;
    return new Promise((accept, reject) => {
      try {
        const socket = new WebSocket(this.uri, 'com.omegaup.events');

        socket.onmessage = (message) => this.onmessage(message);
        socket.onopen = () => {
          this.shouldRetry = true;
          this.socketStatus = SocketStatus.Ok;
          this.socketKeepalive = setInterval(
            () => socket.send('"ping"'),
            30000,
          );
          accept();
        };
        socket.onclose = (e: Event) => {
          this.onclose();
          reject(e);
        };

        this.socket = socket;
      } catch (e) {
        reject(e);
        return;
      }
    });
  }

  onmessage(message: MessageEvent) {
    const data = JSON.parse(message.data);

    if (data.message == '/run/update/') {
      data.run.time = time.remoteTime(data.run.time * 1000);
      updateRun(data.run);
    } else if (data.message == '/clarification/update/') {
      data.clarification.time = time.remoteTime(data.clarification.time * 1000);
      clarificationStore.commit('addClarification', data.clarification);
    } else if (data.message == '/scoreboard/update/') {
      data.time = time.remoteTime(data.time * 1000);
      // TODO: Uncomment next block when virtual contest is migrated
      /*if (problemsetAdmin && data.scoreboard_type != 'admin') {
        if (options.originalContestAlias == null) return;
        virtualRankingChange(data.scoreboard);
        return;
      }*/
      onRankingChanged({ scoreboard: data.scoreboard });

      api.Problemset.scoreboardEvents({
        problemset_id: this.problemsetId,
        token: this.scoreboardToken,
      })
        .then((response) => onRankingEvents(response.events))
        .catch(ui.ignoreError);
    }
  }

  onclose() {
    this.socket = null;
    if (this.socketKeepalive) {
      clearInterval(this.socketKeepalive);
      this.socketKeepalive = null;
    }
    if (this.shouldRetry && this.retries > 0) {
      this.retries--;
      this.socketStatus = SocketStatus.Waiting;
      setTimeout(() => this.connect(), Math.random() * 15000);
      return;
    }
    this.socketStatus = SocketStatus.Failed;
  }

  connectSocket(): void {
    if (this.disableSockets || this.problemsetAlias == 'admin') {
      this.socketStatus = SocketStatus.Failed;
      return;
    }

    this.uri =
      `${window.location.protocol === 'https:' ? 'wss:' : 'ws:'}//${
        window.location.host
      }/events/?filter=/problemset/${this.problemsetId}` +
      (this.scoreboardToken ? `/${this.scoreboardToken}` : '');
    this.socketStatus = SocketStatus.Waiting;
    ui.reportEvent('events-socket', 'attempt');

    this.connect()
      .then(() => {
        ui.reportEvent('events-socket', 'connected');
      })
      .catch((e) => {
        ui.reportEvent('events-socket', 'fallback');
        console.log(e);
        setTimeout(() => {
          this.setupPolls();
        }, Math.random() * 15000);
      });
  }

  setupPolls(): void {
    api.Problemset.scoreboard({
      problemset_id: this.problemsetId,
      token: this.scoreboardToken,
    })
      .then((scoreboard) => {
        onRankingChanged({ scoreboard });

        api.Problemset.scoreboardEvents({
          problemset_id: this.problemsetId,
          token: this.scoreboardToken,
        })
          .then((response) => onRankingEvents(response.events))
          .catch(ui.ignoreError);
      })
      .catch(ui.ignoreError);
    if (!this.problemsetAlias) {
      return;
    }
    refreshContestClarifications({
      type: ContestClarificationType.AllProblems,
      contestAlias: this.problemsetAlias,
      rowcount: this.clarificationsRowcount,
      offset: this.clarificationsOffset,
    });

    if (!this.socket) {
      this.clarificationInterval = setInterval(() => {
        this.clarificationsOffset = 0; // Return pagination to start on refresh
        if (this.problemsetAlias) {
          refreshContestClarifications({
            type: ContestClarificationType.AllProblems,
            contestAlias: this.problemsetAlias,
            rowcount: this.clarificationsRowcount,
            offset: this.clarificationsOffset,
          });
        }
      }, 5 * 60 * 1000);

      this.rankingInterval = setInterval(() => {
        api.Problemset.scoreboard({
          problemset_id: this.problemsetId,
          token: this.scoreboardToken,
        })
          .then((scoreboard) => {
            onRankingChanged({ scoreboard });

            api.Problemset.scoreboardEvents({
              problemset_id: this.problemsetId,
              token: this.scoreboardToken,
            })
              .then((response) => onRankingEvents(response.events))
              .catch(ui.ignoreError);
          })
          .catch(ui.ignoreError);
      }, 5 * 60 * 1000);
    }
  }
}
