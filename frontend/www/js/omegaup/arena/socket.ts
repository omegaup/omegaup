import {
  ContestClarificationType,
  refreshContestClarifications,
} from './clarifications';
import { EventsSocket } from './events_socket';

export enum SocketStatus {
  Waiting = '↻',
  Failed = '✗',
  Ok = '•',
}

export class Socket {
  socket: EventsSocket | null = null;
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

  connectSocket(): boolean {
    if (this.disableSockets || this.problemsetAlias == 'admin') {
      this.socketStatus = SocketStatus.Failed;
      return false;
    }

    const protocol = this.locationProtocol === 'https:' ? 'wss://' : 'ws://';
    const uris = [];
    // Backendv2 uri
    uris.push(
      `${protocol}${this.locationHost}/events/?filter=/problemset/${this.problemsetId}` +
        (this.scoreboardToken ? `/${this.scoreboardToken}` : ''),
    );

    const connect = (uris: string[], index: number) => {
      let socket: EventsSocket | null = new EventsSocket(uris[index]);
      socket.connect().catch((e) => {
        console.log(e);
        // Try the next uri.
        index++;
        if (index < uris.length) {
          connect(uris, index);
        } else {
          // Out of options. Falling back to polls.
          socket = null;
          setTimeout(() => {
            this.setupPolls();
          }, Math.random() * 15000);
        }
      });
    };

    this.socketStatus = SocketStatus.Waiting;
    connect(uris, 0);
    return false;
  }

  setupPolls(): void {
    // TODO: Add refreshRanking function in PR #5230 and then, uncomment next line
    //refreshRanking();
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

      // TODO: Add refreshRanking function in PR #5230 and then, uncomment next block
      /*this.rankingInterval = setInterval(
        () => refreshRanking(),
        5 * 60 * 1000,
      );*/
    }
  }
}
