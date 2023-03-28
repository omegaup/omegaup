import * as time from '../time';
import * as ui from '../ui';
import * as api from '../api';
import {
  ContestClarificationType,
  refreshContestClarifications,
} from './clarifications';
import clarificationStore from './clarificationsStore';
import {
  createChart,
  onRankingChanged,
  onRankingEvents,
  onVirtualRankingChanged,
} from './ranking';
import { updateRun } from './submissions';
import { types } from '../api_types';
import rankingStore from './rankingStore';
import socketStore from './socketStore';
import { ScoreMode } from './navigation';

export enum SocketStatus {
  Waiting = '↻',
  Failed = '✗',
  Connected = '•',
}

export interface SocketOptions {
  disableSockets: boolean;
  problemsetAlias: string;
  isVirtual: boolean;
  originalProblemsetId?: number;
  startTime: Date;
  finishTime?: Date;
  locationProtocol: string;
  locationHost: string;
  problemsetId: number;
  scoreboardToken: null | string;
  clarificationsOffset: number;
  clarificationsRowcount: number;
  currentUsername: string;
  navbarProblems: types.NavbarProblemsetProblem[];
  intervalInMilliseconds: number;
  scoreMode: ScoreMode;
}

export class EventsSocket {
  private readonly uri: string;
  shouldRetry: boolean = false;
  private socket: WebSocket | null = null;
  private socketKeepalive: ReturnType<typeof setTimeout> | null = null;
  retries: number = 10;

  private readonly disableSockets: boolean;
  private readonly problemsetAlias: string;
  private readonly isVirtual: boolean;
  private readonly originalProblemsetId?: number;
  private readonly startTime: Date;
  private readonly finishTime?: Date;
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
  private readonly scoreMode: ScoreMode;

  constructor({
    disableSockets = false,
    problemsetAlias,
    isVirtual = false,
    originalProblemsetId,
    startTime,
    finishTime,
    locationProtocol,
    locationHost,
    problemsetId,
    scoreboardToken = null,
    clarificationsOffset = 0,
    clarificationsRowcount = 20,
    currentUsername,
    navbarProblems,
    intervalInMilliseconds = 5 * 60 * 1000,
    scoreMode = ScoreMode.Partial,
  }: SocketOptions) {
    this.socket = null;

    this.disableSockets = disableSockets;
    this.problemsetAlias = problemsetAlias;
    this.isVirtual = isVirtual;
    this.originalProblemsetId = originalProblemsetId;
    this.startTime = startTime;
    this.finishTime = finishTime;
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
    this.scoreMode = scoreMode;

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
      if (this.scoreMode === ScoreMode.MaxPerGroup) {
        api.Contest.scoreboard({ contest_alias: this.problemsetAlias })
          .then((result: types.Scoreboard) => {
            this.processRankings({
              scoreboard: result,
              currentTime: result.time.getTime(),
              startTime: result.start_time.getTime(),
              finishTime: result.finish_time?.getTime() ?? 0,
            });
          })
          .catch(ui.apiError);
      } else {
        this.processRankings({
          scoreboard: data.scoreboard,
          currentTime: data.scoreboard.time,
          startTime: data.scoreboard.start_time,
          finishTime: data.scoreboard.finish_time,
        });
      }
    }
  }

  private processRankings({
    scoreboard,
    currentTime,
    startTime,
    finishTime,
  }: {
    scoreboard: types.Scoreboard;
    currentTime: number;
    startTime: number;
    finishTime: number;
  }) {
    scoreboard.time = time.remoteTime(currentTime * 1000);
    scoreboard.start_time = time.remoteTime(startTime * 1000);
    if (scoreboard.finish_time != null) {
      scoreboard.finish_time = time.remoteTime(finishTime * 1000);
    }
    if (this.isVirtual) {
      api.Problemset.scoreboardEvents({
        problemset_id: this.originalProblemsetId,
        token: this.scoreboardToken,
      })
        .then((response) => {
          onVirtualRankingChanged({
            scoreboard,
            currentUsername: this.currentUsername,
            scoreboardEvents: response.events,
            problems: this.navbarProblems,
            startTime: this.startTime,
            finishTime: this.finishTime,
            scoreMode: this.scoreMode,
          });
        })
        .catch(ui.ignoreError);
      return;
    }
    const {
      currentRanking,
      ranking,
      users,
      lastTimeUpdated,
    } = onRankingChanged({
      scoreboard,
      currentUsername: this.currentUsername,
      navbarProblems: this.navbarProblems,
      scoreMode: this.scoreMode,
    });
    rankingStore.commit('updateRanking', ranking);
    rankingStore.commit('updateMiniRankingUsers', users);
    rankingStore.commit('updateLastTimeUpdated', lastTimeUpdated);

    api.Problemset.scoreboardEvents({
      problemset_id: this.problemsetId,
      token: this.scoreboardToken,
    })
      .then((response) =>
        this.calculateRankingEvents({
          events: response.events,
          startTimestamp: this.startTime.getTime(),
          finishTimestamp: Date.now(),
          currentRanking,
        }),
      )
      .catch(ui.ignoreError);
  }

  private calculateRankingEvents({
    events,
    currentRanking,
    startTimestamp = 0,
    finishTimestamp = Date.now(),
    placesToShowInChart = 10,
  }: {
    events: types.ScoreboardEvent[];
    currentRanking: { [username: string]: number };
    startTimestamp?: number;
    finishTimestamp?: number;
    placesToShowInChart?: number;
  }) {
    const { series, navigatorData } = onRankingEvents({
      events,
      startTimestamp,
      finishTimestamp,
      currentRanking,
      placesToShowInChart,
    });

    let maxPoints = 0;
    for (const problem of this.navbarProblems) {
      maxPoints += problem.maxScore;
    }

    if (series.length) {
      const rankingChartOptions = createChart({
        series,
        navigatorData,
        startTimestamp: this.startTime.getTime(),
        finishTimestamp: Date.now(),
        maxPoints,
      });
      rankingStore.commit('updateRankingChartOptions', rankingChartOptions);
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
      socketStore.commit('updateSocketStatus', this.socketStatus);
      setTimeout(
        () => this.connectSocket(),
        Math.random() * (this.intervalInMilliseconds / 2),
      );
      return;
    }
    this.socketStatus = SocketStatus.Failed;
    socketStore.commit('updateSocketStatus', this.socketStatus);
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
          socketStore.commit('updateSocketStatus', this.socketStatus);
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
      socketStore.commit('updateSocketStatus', this.socketStatus);
      return;
    }

    this.socketStatus = SocketStatus.Waiting;
    socketStore.commit('updateSocketStatus', this.socketStatus);
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
          scoreMode: this.scoreMode,
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
              scoreMode: this.scoreMode,
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
