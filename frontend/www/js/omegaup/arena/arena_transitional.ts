import Vue from 'vue';
import Vuex, { StoreOptions } from 'vuex';

import { types } from '../api_types';
import T from '../lang';
import { OmegaUp } from '../omegaup';
import * as time from '../time';

Vue.use(Vuex);

export const scoreboardColors = [
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

export interface ArenaOptions {
  assignmentAlias: string | null;
  contestAlias: string | null;
  courseAlias: string | null;
  disableClarifications: boolean;
  disableSockets: boolean;
  isInterview: boolean;
  isLockdownMode: boolean;
  isOnlyProblem: boolean;
  isPractice: boolean;
  onlyProblemAlias: string | null;
  originalContestAlias: string | null;
  payload: types.CommonPayload;
  problemsetId: number | null;
  preferredLanguage: string | null;
  scoreboardToken: string | null;
  shouldShowFirstAssociatedIdentityRunWarning: boolean;
}

// Forward-declaring Arena until we can decouple the EventSocket.
declare class Arena {
  elements: { socketStatus: JQuery };
  options: ArenaOptions;
  problemsetAdmin: boolean;

  rankingChange(data: types.Scoreboard, rankingEvent?: boolean): void;
  updateRun(run: Partial<types.Run>): void;
  updateClarification(clarification: types.Clarification): void;
  virtualRankingChange(data: types.Scoreboard): void;
}

export interface RunsState {
  // The list of runs.
  runs: types.Run[];

  // The mapping of run GUIDs to indices on the runs array.
  index: Record<string, number>;
}

export const runsStore = new Vuex.Store<RunsState>({
  state: {
    runs: [],
    index: {},
  },
  mutations: {
    addRun(state, run: types.Run) {
      if (state.index.hasOwnProperty(run.guid)) {
        Vue.set(
          state.runs,
          state.index[run.guid],
          Object.assign({}, state.runs[state.index[run.guid]], run),
        );
        return;
      }
      Vue.set(state.index, run.guid, state.runs.length);
      state.runs.push(run);
    },
    clear(state) {
      state.runs.splice(0);
      state.index = {};
    },
  },
});

export const myRunsStore = new Vuex.Store<RunsState>({
  state: {
    runs: [],
    index: {},
  },
  mutations: {
    addRun(state, run: types.Run) {
      if (state.index.hasOwnProperty(run.guid)) {
        Vue.set(
          state.runs,
          state.index[run.guid],
          Object.assign({}, state.runs[state.index[run.guid]], run),
        );
        return;
      }
      Vue.set(state.index, run.guid, state.runs.length);
      state.runs.push(run);
    },
    clear(state) {
      state.runs.splice(0);
      state.index = {};
    },
  },
});

export function GetOptionsFromLocation(arenaLocation: Location): ArenaOptions {
  const options: ArenaOptions = {
    isLockdownMode: false,
    isInterview: false,
    isPractice: false,
    isOnlyProblem: false,
    disableClarifications: false,
    disableSockets: false,
    assignmentAlias: null,
    contestAlias: null,
    courseAlias: null,
    scoreboardToken: null,
    shouldShowFirstAssociatedIdentityRunWarning: false,
    onlyProblemAlias: null,
    originalContestAlias: null,
    problemsetId: null,
    payload: {
      omegaUpLockDown: false,
      bootstrap4: false,
      inContest: false,
      isLoggedIn: false,
      isReviewer: false,
      gravatarURL51: '',
      currentUsername: '',
      userClassname: 'user-rank-unranked',
      profileProgress: 0,
      isMainUserIdentity: false,
      isAdmin: false,
      lockDownImage: '',
      navbarSection: '',
    },
    preferredLanguage: null,
  };

  if (
    document.getElementsByTagName('body')[0].className.indexOf('lockdown') !==
    -1
  ) {
    options.isLockdownMode = true;
    window.onbeforeunload = (e: BeforeUnloadEvent) => {
      e.preventDefault();
      e.returnValue = T.lockdownMessageWarning;
    };
  }

  if (arenaLocation.pathname.indexOf('/practice') !== -1) {
    options.isPractice = true;
  }

  if (arenaLocation.pathname.indexOf('/arena/problem/') !== -1) {
    options.isOnlyProblem = true;
    const match = /\/arena\/problem\/([^\/]+)\/?/.exec(arenaLocation.pathname);
    if (match) {
      options.onlyProblemAlias = match[1];
    }
  } else {
    const match = /\/arena\/([^\/]+)\/?/.exec(arenaLocation.pathname);
    if (match) {
      options.contestAlias = match[1];
    }
  }

  if (arenaLocation.search.indexOf('ws=off') !== -1) {
    options.disableSockets = true;
  }
  if (document.getElementById('payload')) {
    const payload = <
      types.CommonPayload & {
        shouldShowFirstAssociatedIdentityRunWarning?: boolean;
        preferred_language?: string;
      }
    >types.payloadParsers.CommonPayload();
    if (payload !== null) {
      options.shouldShowFirstAssociatedIdentityRunWarning =
        payload.shouldShowFirstAssociatedIdentityRunWarning || false;
      options.preferredLanguage = payload.preferred_language || null;
      options.payload = payload;
    }
  }
  return options;
}

export function getMaxScore(
  runs: types.Run[],
  alias: string,
  previousScore: number,
): number {
  let maxScore = previousScore;
  for (const run of runs) {
    if (alias != run.alias) {
      continue;
    }
    const score = run.contest_score || 0;
    if (score > maxScore) {
      maxScore = score;
    }
  }
  return maxScore;
}

export class EventsSocket {
  uri: string;
  arena: Arena;
  shouldRetry: boolean = false;
  socket: WebSocket | null = null;
  socketKeepalive: ReturnType<typeof setTimeout> | null = null;
  retries: number = 10;

  constructor(uri: string, arena: Arena) {
    this.uri = uri;
    this.arena = arena;
  }

  connect(): Promise<void> {
    this.shouldRetry = false;
    return new Promise((accept, reject) => {
      try {
        const socket = new WebSocket(this.uri, 'com.omegaup.events');

        socket.onmessage = message => this.onmessage(message);
        socket.onopen = (e: Event) => {
          this.shouldRetry = true;
          this.arena.elements.socketStatus.html('&bull;').css('color', '#080');
          this.socketKeepalive = setInterval(
            () => socket.send('"ping"'),
            30000,
          );
          accept();
        };
        socket.onclose = (e: Event) => {
          this.onclose(e);
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
      this.arena.updateRun(data.run);
    } else if (data.message == '/clarification/update/') {
      if (!this.arena.options.disableClarifications) {
        data.clarification.time = time.remoteTime(
          data.clarification.time * 1000,
        );
        this.arena.updateClarification(data.clarification);
      }
    } else if (data.message == '/scoreboard/update/') {
      data.time = time.remoteTime(data.time * 1000);
      if (this.arena.problemsetAdmin && data.scoreboard_type != 'admin') {
        if (this.arena.options.originalContestAlias == null) return;
        this.arena.virtualRankingChange(data.scoreboard);
        return;
      }
      this.arena.rankingChange(data.scoreboard);
    }
  }

  onclose(e: Event) {
    this.socket = null;
    if (this.socketKeepalive) {
      clearInterval(this.socketKeepalive);
      this.socketKeepalive = null;
    }
    if (this.shouldRetry && this.retries > 0) {
      this.retries--;
      this.arena.elements.socketStatus.html('↻').css('color', '#888');
      setTimeout(() => this.connect(), Math.random() * 15000);
      return;
    }

    this.arena.elements.socketStatus.html('✗').css('color', '#800');
  }
}

export class EphemeralGrader {
  ephemeralEmbeddedGraderElement: null | HTMLIFrameElement;
  messageQueue: { method: string; params: any[] }[];
  loaded: boolean;

  constructor() {
    this.ephemeralEmbeddedGraderElement = <null | HTMLIFrameElement>(
      document.getElementById('ephemeral-embedded-grader')
    );
    this.messageQueue = [];
    this.loaded = false;

    if (!this.ephemeralEmbeddedGraderElement) return;

    this.ephemeralEmbeddedGraderElement.onload = () => {
      this.loaded = true;
      this.messageQueue.slice(0).forEach(message => {
        this._sendInternal(message);
      });
    };
  }

  send(method: string, ...params: any[]): void {
    let message = {
      method: method,
      params: params,
    };

    if (!this.loaded) {
      this.messageQueue.push(message);
      return;
    }
    this._sendInternal(message);
  }

  _sendInternal(message: { method: string; params: any[] }): void {
    if (!this.ephemeralEmbeddedGraderElement) {
      return;
    }
    (<Window>this.ephemeralEmbeddedGraderElement.contentWindow).postMessage(
      message,
      window.location.origin + '/grader/ephemeral/embedded/',
    );
  }
}
