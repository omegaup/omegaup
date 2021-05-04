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
import { types } from '../api_types';

export enum SocketStatus {
  Waiting = '↻',
  Failed = '✗',
  Connected = '•',
}

export interface SocketOptions {
  disableSockets: boolean;
  problemsetAlias: string;
  locationProtocol: string;
  locationHost: string;
  problemsetId: number;
  scoreboardToken: null | string;
  clarificationsOffset: number;
  clarificationsRowcount: number;
  currentUsername: string;
  navbarProblems: types.NavbarProblemsetProblem[];
  intervalInMilliseconds: number;
}

export class EventsSocket {
  private readonly uri: string;
  shouldRetry: boolean = false;
  private socket: WebSocket | null = null;
  private socketKeepalive: ReturnType<typeof setTimeout> | null = null;
  retries: number = 10;

  private readonly disableSockets: boolean;
  private readonly problemsetAlias: string;
  private readonly problemsetId: number;
  private readonly scoreboardToken: null | string;
  socketStatus: SocketStatus = SocketStatus.Waiting;
  private clarificationInterval: ReturnType<typeof setTimeout> | null = null;
  private rankingInterval: ReturnType<typeof setTimeout> | null = null;
  private clarificationsOffset: number;
  private readonly clarificationsRowcount: number;
  private readonly currentUsername: string;
  private readonly navbarProblems: types.NavbarProblemsetProblem[];
  private readonly intervalInMilliseconds: number;

  constructor({
    disableSockets = false,
    problemsetAlias,
    locationProtocol,
    locationHost,
    problemsetId,
    scoreboardToken = null,
    clarificationsOffset = 0,
    clarificationsRowcount = 20,
    currentUsername,
    navbarProblems,
    intervalInMilliseconds = 5 * 60 * 1000,
  }: SocketOptions) {
    this.socket = null;

    this.disableSockets = disableSockets;
    this.problemsetAlias = problemsetAlias;
    this.problemsetId = problemsetId;
    this.scoreboardToken = scoreboardToken;
    this.clarificationsOffset = clarificationsOffset;
    this.clarificationsRowcount = clarificationsRowcount;
    this.currentUsername = currentUsername;
    this.navbarProblems = navbarProblems;
    this.socketStatus = SocketStatus.Waiting;
    this.clarificationInterval = null;
    this.rankingInterval = null;
    this.intervalInMilliseconds = intervalInMilliseconds;

    const protocol = locationProtocol === 'https:' ? 'wss:' : 'ws:';
    const host = locationHost;
    this.uri = `${protocol}//${host}/events/?filter=/problemset/${problemsetId}`;
    if (this.scoreboardToken) {
      this.uri = this.uri.concat('/', this.scoreboardToken);
    }
  }

  private onmessage(message: MessageEvent) {
    const data = JSON.parse(message.data);

    if (data.message == '/run/update/') {
      data.run.time = time.remoteTime(data.run.time * 1000);
      updateRun({ run: data.run });
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
      const { currentRanking } = onRankingChanged({
        scoreboard: data.scoreboard,
        currentUsername: this.currentUsername,
        navbarProblems: this.navbarProblems,
      });

      api.Problemset.scoreboardEvents({
        problemset_id: this.problemsetId,
        token: this.scoreboardToken,
      })
        .then((response) =>
          onRankingEvents({
            events: response.events,
            currentRanking,
          }),
        )
        .catch(ui.ignoreError);
    }
  }

  private onclose() {
    this.socket = null;
    if (this.socketKeepalive) {
      clearInterval(this.socketKeepalive);
      this.socketKeepalive = null;
    }
    if (this.shouldRetry && this.retries > 0) {
      this.retries--;
      this.socketStatus = SocketStatus.Waiting;
      setTimeout(
        () => this.connectSocket(),
        Math.random() * (this.intervalInMilliseconds / 2),
      );
      return;
    }
    this.socketStatus = SocketStatus.Failed;
  }

  private connectSocket(): Promise<void> {
    this.shouldRetry = false;
    return new Promise((accept, reject) => {
      try {
        const socket = new WebSocket(this.uri, 'com.omegaup.events');

        socket.onmessage = (message) => this.onmessage(message);
        socket.onopen = () => {
          this.shouldRetry = true;
          this.socketStatus = SocketStatus.Connected;
          this.socketKeepalive = setInterval(
            () => socket.send('"ping"'),
            this.intervalInMilliseconds,
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

  connect(): void {
    if (this.disableSockets || this.problemsetAlias === 'admin') {
      this.socketStatus = SocketStatus.Failed;
      return;
    }

    this.socketStatus = SocketStatus.Waiting;
    ui.reportEvent('events-socket', 'attempt');

    this.connectSocket()
      .then(() => {
        ui.reportEvent('events-socket', 'connected');
      })
      .catch((e) => {
        ui.reportEvent('events-socket', 'fallback');
        console.log(e);
        setTimeout(() => {
          this.setupPolls();
        }, Math.random() * (this.intervalInMilliseconds / 2));
      });
  }

  private setupPolls(): void {
    api.Problemset.scoreboard({
      problemset_id: this.problemsetId,
      token: this.scoreboardToken,
    })
      .then((scoreboard) => {
        const { currentRanking } = onRankingChanged({
          scoreboard,
          currentUsername: this.currentUsername,
          navbarProblems: this.navbarProblems,
        });

        api.Problemset.scoreboardEvents({
          problemset_id: this.problemsetId,
          token: this.scoreboardToken,
        })
          .then((response) =>
            onRankingEvents({
              events: response.events,
              currentRanking,
            }),
          )
          .catch(ui.ignoreError);
      })
      .catch(ui.ignoreError);
    if (!this.problemsetAlias) {
      return;
    }
    refreshContestClarifications({
      type: ContestClarificationType.AllProblems,
      contestAlias: this.problemsetAlias,
      offset: this.clarificationsOffset,
      rowcount: this.clarificationsRowcount,
    });

    if (!this.socket) {
      this.clarificationInterval = setInterval(() => {
        this.clarificationsOffset = 0; // Return pagination to start on refresh
        if (this.problemsetAlias) {
          refreshContestClarifications({
            type: ContestClarificationType.AllProblems,
            contestAlias: this.problemsetAlias,
            offset: this.clarificationsOffset,
            rowcount: this.clarificationsRowcount,
          });
        }
      }, this.intervalInMilliseconds);

      this.rankingInterval = setInterval(() => {
        api.Problemset.scoreboard({
          problemset_id: this.problemsetId,
          token: this.scoreboardToken,
        })
          .then((scoreboard) => {
            const { currentRanking } = onRankingChanged({
              scoreboard,
              currentUsername: this.currentUsername,
              navbarProblems: this.navbarProblems,
            });

            api.Problemset.scoreboardEvents({
              problemset_id: this.problemsetId,
              token: this.scoreboardToken,
            })
              .then((response) =>
                onRankingEvents({
                  events: response.events,
                  currentRanking,
                }),
              )
              .catch(ui.ignoreError);
          })
          .catch(ui.ignoreError);
      }, this.intervalInMilliseconds);
    }
  }
}
