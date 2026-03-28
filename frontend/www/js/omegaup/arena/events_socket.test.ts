jest.mock('../../../third_party/js/diff_match_patch.js');
jest.mock('./ranking');

import { types } from '../api_types';
import { OmegaUp } from '../omegaup';
import { SocketOptions, SocketStatus, EventsSocket } from './events_socket';
import WS from 'jest-websocket-mock';
import { runsStoreConfig } from './runsStore';
import { clarificationStoreConfig } from './clarificationsStore';
import { createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import fetchMock from 'jest-fetch-mock';
import { onRankingChanged, onRankingEvents } from './ranking';
import { mocked } from 'ts-jest/utils';
import { ScoreMode } from './navigation';

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
  isVirtual: false,
  startTime: new Date(0),
  finishTime: new Date(1),
  locationProtocol: 'http',
  locationHost: 'localhost:1234',
  problemsetId: 1,
  scoreboardToken: 'token',
  clarificationsOffset: 1,
  clarificationsRowcount: 30,
  navbarProblems: navbarProblems,
  currentUsername: 'omegaUp',
  intervalInMilliseconds: 500,
  scoreMode: ScoreMode.Partial,
};
describe('EventsSocket', () => {
  let server: WS | null = null;

  beforeEach(() => {
    OmegaUp.ready = true;
    jest.useFakeTimers();
    server = new WS(`ws://${options.locationHost}/events/`, {
      selectProtocol: () => 'com.omegaup.events',
      jsonProtocol: true,
    });

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
  });

  afterEach(() => {
    jest.runOnlyPendingTimers();
    jest.useRealTimers();
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

  it('should handle a socket when it is disabled', () => {
    const socket = new EventsSocket({ ...options, disableSockets: true });
    expect(socket.shouldRetry).toEqual(false);
    expect(socket.retries).toEqual(10);
    expect(socket.socketStatus).toEqual(SocketStatus.Waiting);

    socket.connect();
    expect(socket.socketStatus).toEqual(SocketStatus.Failed);
  });

  it('should handle a socket successfully connected', async () => {
    const socket = new EventsSocket({ ...options, disableSockets: false });
    socket.connect();
    jest.runOnlyPendingTimers();
    await server?.connected;
    expect(socket.socketStatus).toEqual(SocketStatus.Connected);
  });

  it('should handle a socket when it is closed', async () => {
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

  it('should clear intervals when the connection is closed', async () => {
    const client = new EventsSocket({ ...options, disableSockets: false });

    // Step 1: Simulate socket failing to connect (fallback path)
    // setupPolls is called internally; we replicate that here by setting socket=null
    (client as any).socket = null;
    (client as any).setupPolls();

    // Step 2: clarificationInterval and rankingInterval are now active
    expect((client as any).clarificationInterval).not.toBeNull();
    expect((client as any).rankingInterval).not.toBeNull();

    // Step 3: Socket reconnects — fresh server accepts the connection
    WS.clean();
    const freshServer = new WS(`ws://${options.locationHost}/events/`, {
      selectProtocol: () => 'com.omegaup.events',
      jsonProtocol: true,
    });

    client.shouldRetry = true;
    (client as any).connectSocket().catch(() => {});
    jest.runOnlyPendingTimers();
    await freshServer.connected;

    // Step 4: onopen fires — polling intervals must be cleared
    expect((client as any).clarificationInterval).toBeNull();
    expect((client as any).rankingInterval).toBeNull();
  });

  it('should not leak polling intervals during reconnect cycles with setupPolls', async () => {
    const client = new EventsSocket({ ...options, disableSockets: false });

    // === Cycle 1: fail → poll → reconnect → clear ===

    // Simulate connection failure / fallback
    (client as any).socket = null;
    (client as any).setupPolls();

    const clarificationInterval1 = (client as any).clarificationInterval;
    const rankingInterval1 = (client as any).rankingInterval;

    expect(clarificationInterval1).not.toBeNull();
    expect(rankingInterval1).not.toBeNull();

    // Reconnect — fresh server accepts the connection
    WS.clean();
    let freshServer = new WS(`ws://${options.locationHost}/events/`, {
      selectProtocol: () => 'com.omegaup.events',
      jsonProtocol: true,
    });
    client.shouldRetry = true;
    (client as any).connectSocket().catch(() => {});
    jest.runOnlyPendingTimers();
    await freshServer.connected;

    // Intervals must be cleared after onopen
    expect((client as any).clarificationInterval).toBeNull();
    expect((client as any).rankingInterval).toBeNull();

    // === Cycle 2: fail → poll → reconnect → clear ===

    // Capture current (null) before next failure
    const prevClarification2 = (client as any).clarificationInterval;
    const prevRanking2 = (client as any).rankingInterval;

    // Simulate next failure and fallback
    freshServer.close();
    (client as any).socket = null;
    (client as any).setupPolls();

    // New intervals must be non-null and different from the previous (null) ones
    expect((client as any).clarificationInterval).not.toBeNull();
    expect((client as any).rankingInterval).not.toBeNull();
    expect((client as any).clarificationInterval).not.toBe(prevClarification2);
    expect((client as any).rankingInterval).not.toBe(prevRanking2);

    // Reconnect again
    WS.clean();
    freshServer = new WS(`ws://${options.locationHost}/events/`, {
      selectProtocol: () => 'com.omegaup.events',
      jsonProtocol: true,
    });
    client.shouldRetry = true;
    (client as any).connectSocket().catch(() => {});
    jest.runOnlyPendingTimers();
    await freshServer.connected;

    expect((client as any).clarificationInterval).toBeNull();
    expect((client as any).rankingInterval).toBeNull();

    // === Cycle 3: fail → poll → reconnect → clear ===

    const prevClarification3 = (client as any).clarificationInterval;
    const prevRanking3 = (client as any).rankingInterval;

    freshServer.close();
    (client as any).socket = null;
    (client as any).setupPolls();

    expect((client as any).clarificationInterval).not.toBeNull();
    expect((client as any).rankingInterval).not.toBeNull();
    expect((client as any).clarificationInterval).not.toBe(prevClarification3);
    expect((client as any).rankingInterval).not.toBe(prevRanking3);

    // Reconnect a third time
    WS.clean();
    freshServer = new WS(`ws://${options.locationHost}/events/`, {
      selectProtocol: () => 'com.omegaup.events',
      jsonProtocol: true,
    });
    client.shouldRetry = true;
    (client as any).connectSocket().catch(() => {});
    jest.runOnlyPendingTimers();
    await freshServer.connected;

    expect((client as any).clarificationInterval).toBeNull();
    expect((client as any).rankingInterval).toBeNull();

    // Clean up keepalive timer so afterEach's runOnlyPendingTimers doesn't fire on a closed socket
    (client as any).onclose();
  });

  it('should handle a socket when server sends /run/update/ message', async () => {
    const socket = new EventsSocket({ ...options, disableSockets: false });

    socket.connect();
    jest.runOnlyPendingTimers();
    await server?.connected;

    const localVue = createLocalVue();
    localVue.use(Vuex);
    const store = new Vuex.Store(runsStoreConfig);

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
      } as types.Run,
    });

    expect(socket.socketStatus).toEqual(SocketStatus.Connected);
    expect(store.state.runs).toHaveLength(1);
    expect(store.state.runs[0]['alias']).toBe('hello');
    expect(store.state.runs[0]['classname']).toBe('user-rank-unranked');
  });

  it('should handle a socket when server sends /clarification/update/ message', async () => {
    const socket = new EventsSocket({ ...options, disableSockets: false });

    socket.connect();
    jest.runOnlyPendingTimers();
    await server?.connected;

    const localVue = createLocalVue();
    localVue.use(Vuex);
    const clarificationStore = new Vuex.Store(clarificationStoreConfig);

    server?.send({
      message: '/clarification/update/',
      clarification: {
        answer: 'some answer',
        assignment_alias: 'assignment_01',
        author: 'user_1',
        clarification_id: 1,
        contest_alias: 'contest_alias',
        message: 'some message',
        problem_alias: 'problem_alias',
        public: true,
        time: new Date(0),
      } as types.Clarification,
    });

    expect(socket.socketStatus).toEqual(SocketStatus.Connected);
    expect(clarificationStore.state.clarifications).toHaveLength(1);
    expect(clarificationStore.state.clarifications[0]['answer']).toBe(
      'some answer',
    );
    expect(clarificationStore.state.clarifications[0]['message']).toBe(
      'some message',
    );
  });

  it('should handle a socket when server sends /scoreboard/update/ message', async () => {
    const socket = new EventsSocket({ ...options, disableSockets: false });

    socket.connect();
    jest.runOnlyPendingTimers();
    await server?.connected;

    const onRankingChangedMock = mocked(onRankingChanged, true);
    const onRankingEventsMock = mocked(onRankingEvents, false);
    onRankingChangedMock.mockReturnValueOnce({
      users: [],
      ranking: [],
      currentRanking: { omegaUp: 0 },
      maxPoints: 300,
      lastTimeUpdated: new Date(0),
    });
    onRankingEventsMock.mockReturnValueOnce({
      series: [
        {
          type: 'line',
          rank: 0,
          data: [
            [1674625311000, 0],
            [1674625378000, 0],
            [1674625378734, 100],
          ],
          name: 'test-user',
          step: 'right',
        },
      ],
      navigatorData: [
        [1674625311000, 0],
        [1674625378000, 0],
        [1674625378734, 100],
      ],
    });
    server?.send({
      message: '/scoreboard/update/',
      scoreboard: {
        problems: [
          { alias: 'problem_1', order: 1 },
          { alias: 'problem_2', order: 2 },
          { alias: 'problem_3', order: 3 },
        ],
        ranking: [
          {
            classname: 'user-rank-unranked',
            country: 'MX',
            is_invited: true,
            problems: [
              {
                alias: 'problem_1',
                penalty: 20,
                percent: 1,
                points: 100,
                runs: 1,
              },
              {
                alias: 'problem_2',
                penalty: 10,
                percent: 1,
                points: 100,
                runs: 4,
              },
              {
                alias: 'problem_3',
                penalty: 30,
                percent: 1,
                points: 100,
                runs: 5,
              },
            ],
            total: { penalty: 20, points: 100 },
            username: 'omegaUp',
          },
        ],
        title: 'omegaUp',
        time: 0,
        start_time: 0,
        finish_time: 0,
      },
    });

    expect(socket.socketStatus).toEqual(SocketStatus.Connected);

    expect(onRankingChanged).toHaveBeenCalledWith({
      currentUsername: 'omegaUp',
      scoreMode: ScoreMode.Partial,
      navbarProblems: [
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
      ],
      scoreboard: {
        finish_time: new Date(0),
        problems: [
          {
            alias: 'problem_1',
            order: 1,
          },
          {
            alias: 'problem_2',
            order: 2,
          },
          {
            alias: 'problem_3',
            order: 3,
          },
        ],
        ranking: [
          {
            classname: 'user-rank-unranked',
            country: 'MX',
            is_invited: true,
            problems: [
              {
                alias: 'problem_1',
                penalty: 20,
                percent: 1,
                points: 100,
                runs: 1,
              },
              {
                alias: 'problem_2',
                penalty: 10,
                percent: 1,
                points: 100,
                runs: 4,
              },
              {
                alias: 'problem_3',
                penalty: 30,
                percent: 1,
                points: 100,
                runs: 5,
              },
            ],
            total: {
              penalty: 20,
              points: 100,
            },
            username: 'omegaUp',
          },
        ],
        start_time: new Date(0),
        time: new Date(0),
        title: 'omegaUp',
      },
    });
    expect(onRankingChanged).toHaveBeenCalled();
  });
});
