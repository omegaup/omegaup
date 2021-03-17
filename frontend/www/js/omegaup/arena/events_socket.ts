import * as time from '../time';
import clarificationStore from './clarificationsStore';
import { Socket, SocketStatus } from './socket';
import { updateRun } from './submissions';

export class EventsSocket {
  uri: string;
  shouldRetry: boolean = false;
  socket: WebSocket | null = null;
  socketKeepalive: ReturnType<typeof setTimeout> | null = null;
  retries: number = 10;

  constructor(uri: string) {
    this.uri = uri;
  }

  connect(): Promise<void> {
    this.shouldRetry = false;
    return new Promise((accept, reject) => {
      try {
        const webSocket = new WebSocket(this.uri, 'com.omegaup.events');
        const socket = new Socket();

        webSocket.onmessage = (message) => this.onmessage(message);
        webSocket.onopen = () => {
          this.shouldRetry = true;
          socket.socketStatus = SocketStatus.Ok;
          this.socketKeepalive = setInterval(
            () => webSocket.send('"ping"'),
            30000,
          );
          accept();
        };
        webSocket.onclose = (e: Event) => {
          this.onclose(e);
          reject(e);
        };

        this.socket = webSocket;
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
      // TODO: Add refreshRanking function in PR #5230 and then, uncomment next line
      //rankingChange(data.scoreboard);
    }
  }

  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  onclose(e: Event) {
    const socket = new Socket();
    this.socket = null;
    if (this.socketKeepalive) {
      clearInterval(this.socketKeepalive);
      this.socketKeepalive = null;
    }
    if (this.shouldRetry && this.retries > 0) {
      this.retries--;
      socket.socketStatus = SocketStatus.Waiting;
      setTimeout(() => this.connect(), Math.random() * 15000);
      return;
    }
    socket.socketStatus = SocketStatus.Failed;
  }
}
