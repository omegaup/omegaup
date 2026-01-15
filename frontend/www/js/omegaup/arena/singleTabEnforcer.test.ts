// Copyright (c) 2014-2025 omegaUp. All rights reserved.
// Use of this source code is governed by the BSD-style license found in
// the LICENSE file in the root directory of the source tree.

jest.mock('../../../third_party/js/diff_match_patch.js');

import { SingleTabEnforcer, enforceSingleTab } from './singleTabEnforcer';

// Mock BroadcastChannel
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
    // Simulate message being received by other channels with the same name
    for (const instance of MockBroadcastChannel.instances) {
      if (
        instance !== this &&
        instance.name === this.name &&
        !instance.closed
      ) {
        setTimeout(() => {
          if (instance.onmessage) {
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

// Set up global mock
((global as unknown) as {
  BroadcastChannel: typeof MockBroadcastChannel;
}).BroadcastChannel = MockBroadcastChannel;

describe('SingleTabEnforcer', () => {
  beforeEach(() => {
    MockBroadcastChannel.reset();
    jest.useFakeTimers();
  });

  afterEach(() => {
    jest.useRealTimers();
  });

  it('should create channel with correct name', () => {
    const enforcer = new SingleTabEnforcer({
      contestAlias: 'test-contest',
      onBlocked: jest.fn(),
    });

    enforcer.init();

    expect(MockBroadcastChannel.instances).toHaveLength(1);
    expect(MockBroadcastChannel.instances[0].name).toBe(
      'omegaup-contest-test-contest',
    );

    enforcer.destroy();
  });

  it('should not be blocked when first tab opens', () => {
    const onBlocked = jest.fn();
    const enforcer = new SingleTabEnforcer({
      contestAlias: 'test-contest',
      onBlocked,
    });

    enforcer.init();

    expect(enforcer.blocked).toBe(false);
    expect(onBlocked).not.toHaveBeenCalled();

    enforcer.destroy();
  });

  it('should block second tab when first tab is open', async () => {
    const onBlocked1 = jest.fn();
    const onBlocked2 = jest.fn();

    // First tab
    const enforcer1 = new SingleTabEnforcer({
      contestAlias: 'test-contest',
      onBlocked: onBlocked1,
    });
    enforcer1.init();

    // Second tab
    const enforcer2 = new SingleTabEnforcer({
      contestAlias: 'test-contest',
      onBlocked: onBlocked2,
    });
    enforcer2.init();

    // Run timers to allow messages to be delivered
    jest.runAllTimers();

    expect(enforcer1.blocked).toBe(false);
    expect(enforcer2.blocked).toBe(true);
    expect(onBlocked1).not.toHaveBeenCalled();
    expect(onBlocked2).toHaveBeenCalled();

    enforcer1.destroy();
    enforcer2.destroy();
  });

  it('should allow tabs for different contests', () => {
    const onBlocked1 = jest.fn();
    const onBlocked2 = jest.fn();

    // First contest
    const enforcer1 = new SingleTabEnforcer({
      contestAlias: 'contest-a',
      onBlocked: onBlocked1,
    });
    enforcer1.init();

    // Different contest
    const enforcer2 = new SingleTabEnforcer({
      contestAlias: 'contest-b',
      onBlocked: onBlocked2,
    });
    enforcer2.init();

    jest.runAllTimers();

    expect(enforcer1.blocked).toBe(false);
    expect(enforcer2.blocked).toBe(false);
    expect(onBlocked1).not.toHaveBeenCalled();
    expect(onBlocked2).not.toHaveBeenCalled();

    enforcer1.destroy();
    enforcer2.destroy();
  });

  it('should clean up on destroy', () => {
    const enforcer = new SingleTabEnforcer({
      contestAlias: 'test-contest',
      onBlocked: jest.fn(),
    });

    enforcer.init();
    expect(MockBroadcastChannel.instances).toHaveLength(1);

    enforcer.destroy();
    expect(MockBroadcastChannel.instances).toHaveLength(0);
  });
});

describe('enforceSingleTab', () => {
  beforeEach(() => {
    MockBroadcastChannel.reset();
  });

  it('should create and initialize an enforcer', () => {
    const onBlocked = jest.fn();
    const enforcer = enforceSingleTab('my-contest', onBlocked);

    expect(enforcer).not.toBeNull();
    expect(MockBroadcastChannel.instances).toHaveLength(1);

    enforcer.destroy();
  });
});
