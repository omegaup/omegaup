import { broadcastLogout, initLogoutListener } from './logoutSync';

class MockBroadcastChannel {
  static instances: MockBroadcastChannel[] = [];
  name: string;
  onmessage: ((event: MessageEvent) => void) | null = null;
  closed = false;

  constructor(name: string) {
    this.name = name;
    MockBroadcastChannel.instances.push(this);
  }

  postMessage(message: unknown): void {
    for (const instance of MockBroadcastChannel.instances) {
      if (
        instance !== this &&
        instance.name === this.name &&
        !instance.closed
      ) {
        setTimeout(() => {
          if (!instance.closed && instance.onmessage) {
            instance.onmessage(new MessageEvent('message', { data: message }));
          }
        }, 0);
      }
    }
  }

  close(): void {
    this.closed = true;
    const index = MockBroadcastChannel.instances.indexOf(this);
    if (index > -1) {
      MockBroadcastChannel.instances.splice(index, 1);
    }
  }

  static reset(): void {
    MockBroadcastChannel.instances = [];
  }
}

// Install the mock globally before any imports run.
((global as unknown) as {
  BroadcastChannel: typeof MockBroadcastChannel;
}).BroadcastChannel = MockBroadcastChannel;

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

describe('broadcastLogout', () => {
  beforeEach(() => {
    MockBroadcastChannel.reset();
    jest.useFakeTimers();
  });

  afterEach(() => {
    jest.useRealTimers();
  });

  it('sends a logout message to other tabs', () => {
    const onLogout = jest.fn();

    // Simulate an existing tab that is already listening.
    const cleanup = initLogoutListener(onLogout);

    // A different tab triggers the broadcast (its channel is a new instance).
    broadcastLogout();

    // Allow the async setTimeout in MockBroadcastChannel.postMessage to fire.
    jest.runAllTimers();

    expect(onLogout).toHaveBeenCalledTimes(1);

    cleanup();
  });

  it('closes the broadcast channel immediately after sending', () => {
    broadcastLogout();

    // The channel used inside broadcastLogout is fire-and-close, so it
    // should have removed itself from the instances list.
    expect(MockBroadcastChannel.instances).toHaveLength(0);
  });

  it('does not invoke onLogout in the same-tab listener that broadcast', () => {
    // When the logout page itself has a listener (edge case), it should NOT
    // receive its own message because BroadcastChannel does not echo.
    const onLogout = jest.fn();
    const cleanup = initLogoutListener(onLogout);

    // Manually post from the very listener channel to simulate same-instance
    // scenario – the mock skips sending to self, so onLogout must NOT fire.
    const listenerChannel = MockBroadcastChannel.instances[0];
    listenerChannel.postMessage({ type: 'logout', timestamp: Date.now() });

    jest.runAllTimers();

    // Should not have fired because the mock skips the sender instance.
    expect(onLogout).not.toHaveBeenCalled();

    cleanup();
  });
});

describe('initLogoutListener', () => {
  beforeEach(() => {
    MockBroadcastChannel.reset();
    jest.useFakeTimers();
  });

  afterEach(() => {
    jest.useRealTimers();
  });

  it('creates exactly one BroadcastChannel', () => {
    const cleanup = initLogoutListener(jest.fn());

    expect(MockBroadcastChannel.instances).toHaveLength(1);

    cleanup();
  });

  it('calls onLogout exactly once for multiple logout messages', () => {
    const onLogout = jest.fn();
    initLogoutListener(onLogout);

    // Simulate two rapid logout broadcasts from another "tab".
    const senderChannel = new MockBroadcastChannel('omegaup-logout-sync');
    senderChannel.postMessage({ type: 'logout', timestamp: Date.now() });
    senderChannel.postMessage({ type: 'logout', timestamp: Date.now() });

    jest.runAllTimers();

    // The listener must close itself after the first message, so onLogout
    // should only ever be called once regardless of how many messages arrive.
    expect(onLogout).toHaveBeenCalledTimes(1);
  });

  it('does not call onLogout for unrelated message types', () => {
    const onLogout = jest.fn();
    initLogoutListener(onLogout);

    const senderChannel = new MockBroadcastChannel('omegaup-logout-sync');
    senderChannel.postMessage({ type: 'unknown', timestamp: Date.now() });

    jest.runAllTimers();

    expect(onLogout).not.toHaveBeenCalled();

    // Clean up the open listener channel manually.
    MockBroadcastChannel.instances
      .filter((ch) => ch.name === 'omegaup-logout-sync')
      .forEach((ch) => ch.close());
  });

  it('cleanup closes the channel', () => {
    const cleanup = initLogoutListener(jest.fn());

    expect(MockBroadcastChannel.instances).toHaveLength(1);

    cleanup();

    expect(MockBroadcastChannel.instances).toHaveLength(0);
  });

  it('calling cleanup twice is safe (idempotent)', () => {
    const cleanup = initLogoutListener(jest.fn());

    cleanup();
    // Second call should not throw.
    expect(() => cleanup()).not.toThrow();
  });

  it('returns a no-op cleanup when BroadcastChannel is unavailable', () => {
    // Temporarily remove BroadcastChannel to simulate an unsupported env.
    const original = ((global as unknown) as Record<string, unknown>)
      .BroadcastChannel;
    delete ((global as unknown) as Record<string, unknown>).BroadcastChannel;

    const cleanup = initLogoutListener(jest.fn());

    // Restore immediately.
    ((global as unknown) as Record<
      string,
      unknown
    >).BroadcastChannel = original;

    // No channel should have been created, and cleanup should not throw.
    expect(() => cleanup()).not.toThrow();
  });
});

describe('broadcastLogout + initLogoutListener integration', () => {
  beforeEach(() => {
    MockBroadcastChannel.reset();
    jest.useFakeTimers();
  });

  afterEach(() => {
    jest.useRealTimers();
  });

  it('notifies multiple listening tabs when logout is broadcast', () => {
    const onLogout1 = jest.fn();
    const onLogout2 = jest.fn();

    // Two independent tabs, each with their own listener.
    const cleanup1 = initLogoutListener(onLogout1);
    const cleanup2 = initLogoutListener(onLogout2);

    // A third tab fires the logout.
    broadcastLogout();

    jest.runAllTimers();

    expect(onLogout1).toHaveBeenCalledTimes(1);
    expect(onLogout2).toHaveBeenCalledTimes(1);

    cleanup1();
    cleanup2();
  });
});
