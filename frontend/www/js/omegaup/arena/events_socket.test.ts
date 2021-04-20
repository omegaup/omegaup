jest.mock('../../../third_party/js/diff_match_patch.js');

import { types } from '../api_types';
import { OmegaUp } from '../omegaup';
import { SocketOptions, SocketStatus, EventsSocket } from './events_socket';
import WS from 'jest-websocket-mock';
import { storeConfig } from './runsStore';
import { createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import fetchMock from 'jest-fetch-mock';

const navbarProblems: types.NavbarProblemsetProblem[] = [
  {
    acceptsSubmissions: true,
    alias: 'problem_alias',
    bestScore: 100,
    hasRuns: true,
    maxScore: 100,
    text: 'A. Problem',
  },
  {
    acceptsSubmissions: true,
    alias: 'problem_alias_2',
    bestScore: 80,
    hasRuns: true,
    maxScore: 100,
    text: 'B. Problem 2',
  },
];

const options: SocketOptions = {
  disableSockets: false,
  problemsetAlias: 'hello',
  locationProtocol: 'http',
  locationHost: 'localhost:1234',
  problemsetId: 1,
  scoreboardToken: 'token',
  clarificationsOffset: 1,
  clarificationsRowcount: 30,
  navbarProblems: navbarProblems,
  currentUsername: 'omegaUp',
  intervalInMiliSeconds: 500,
};
describe('EventsSocket', () => {
  let server: WS | null = null;
  beforeEach(() => {
    OmegaUp.ready = true;
    server = new WS(`ws://${options.locationHost}/events/`, {
      selectProtocol: () => 'com.omegaup.events',
      jsonProtocol: true,
    });
  });

  afterEach(() => {
    server = null;
    WS.clean();
  });

  it('can be instantiated', () => {
    const socket = new EventsSocket(options);
    expect(socket.shouldRetry).toEqual(false);
    expect(socket.retries).toEqual(10);
    expect(socket.socketStatus).toEqual(SocketStatus.Waiting);

    socket.connect();
    expect(socket.socketStatus).toEqual(SocketStatus.Waiting);
  });

  it('should handle socket when it is disabled', () => {
    const socket = new EventsSocket({ ...options, disableSockets: true });
    expect(socket.shouldRetry).toEqual(false);
    expect(socket.retries).toEqual(10);
    expect(socket.socketStatus).toEqual(SocketStatus.Waiting);

    socket.connect();
    expect(socket.socketStatus).toEqual(SocketStatus.Failed);
  });

  it('should handle socket when it is closed', async () => {
    const socket = new EventsSocket({ ...options, disableSockets: false });
    socket.connect();
    await server?.connected;
    server?.on('connection', (socket) => {
      socket.close({ wasClean: false, code: 1003, reason: 'any' });
    });
    const client = new EventsSocket({ ...options, disableSockets: true });

    expect(client.shouldRetry).toEqual(false);
    expect(client.retries).toEqual(10);
    expect(client.socketStatus).toEqual(SocketStatus.Waiting);

    client.connect();
    expect(client.socketStatus).toEqual(SocketStatus.Failed);
  });

  it('should handle socket when it sends messages', async () => {
    fetchMock.enableMocks();
    fetchMock.mockIf(/^\/api\/.*/, (req: Request) => {
      if (req.url == '/api/contest/clarifications/') {
        return Promise.resolve({
          status: 200,
          body: JSON.stringify({
            clarifications: [],
            status: 'ok',
          }),
        });
      } else if (req.url == '/api/problemset/scoreboard/') {
        return Promise.resolve({
          status: 200,
          body: JSON.stringify({
            finish_time: null,
            problems: [],
            ranking: [],
            start_time: new Date(),
            time: new Date(),
            title: 'someTitle',
            status: 'ok',
          }),
        });
      } else if (req.url == '/api/problemset/scoreboardEvents/') {
        return Promise.resolve({
          status: 200,
          body: JSON.stringify({
            events: [],
            status: 'ok',
          }),
        });
      }
      return Promise.resolve({
        ok: false,
        status: 404,
        body: JSON.stringify({
          status: 'error',
          error: `Invalid call to "${req.url}" in test`,
          errorcode: 403,
        }),
      });
    });
    const socket = new EventsSocket({ ...options, disableSockets: false });

    expect(socket.shouldRetry).toEqual(false);
    expect(socket.retries).toEqual(10);
    expect(socket.socketStatus).toEqual(SocketStatus.Waiting);

    socket.connect();
    await server?.connected;

    const localVue = createLocalVue();
    localVue.use(Vuex);
    const store = new Vuex.Store(storeConfig);

    server?.send({
      message: '/run/update/',
      run: {
        alias: 'hello',
        classname: 'user-rank-unranked',
        country: 'MX',
        guid: 'abcdef',
        language: 'py2',
        memory: 10240,
        penalty: 20,
        runtime: 1,
        score: 100,
        status: 'ready',
        submit_delay: 1,
        time: new Date(0),
        username: 'omegaUp',
        verdict: 'AC',
      },
    });

    expect(socket.socketStatus).toEqual(SocketStatus.Connected);
    expect(store.state.runs).toHaveLength(1);
    expect(store.state.runs[0].alias).toBe('hello');
    expect(store.state.runs[0].classname).toBe('user-rank-unranked');
  });
});
