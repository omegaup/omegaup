// Copyright (c) 2014-2025 omegaUp. All rights reserved.
// Use of this source code is governed by the BSD-style license found in
// the LICENSE file in the root directory of the source tree.

import T from '../lang';

/**
 * Interface for messages exchanged between tabs via BroadcastChannel.
 */
interface TabMessage {
  type: 'ping' | 'pong';
  timestamp: number;
}

/**
 * Options for configuring the SingleTabEnforcer.
 */
export interface SingleTabEnforcerOptions {
  /** The contest alias used to create a unique channel name */
  contestAlias: string;
  /** Callback invoked when another tab is detected */
  onBlocked: (message: string) => void;
}

/**
 * Enforces that only one browser tab can have a contest open at a time.
 *
 * Uses the BroadcastChannel API to detect when multiple tabs are attempting
 * to access the same contest. When a second tab is opened, it will be blocked
 * and the onBlocked callback will be invoked.
 *
 * @example
 * ```typescript
 * const enforcer = new SingleTabEnforcer({
 *   contestAlias: 'my-contest',
 *   onBlocked: (message) => {
 *     // Show error UI
 *     document.body.innerHTML = `<div class="error">${message}</div>`;
 *   },
 * });
 *
 * // Start enforcement
 * enforcer.init();
 *
 * // Clean up when leaving the page
 * window.addEventListener('beforeunload', () => enforcer.destroy());
 * ```
 */
export class SingleTabEnforcer {
  private channel: BroadcastChannel | null = null;
  private readonly contestAlias: string;
  private readonly onBlocked: (message: string) => void;
  private isBlocked: boolean = false;
  private readonly channelName: string;

  constructor(options: SingleTabEnforcerOptions) {
    this.contestAlias = options.contestAlias;
    this.onBlocked = options.onBlocked;
    this.channelName = `omegaup-contest-${this.contestAlias}`;
  }

  /**
   * Initializes the single tab enforcement.
   *
   * @returns true if this tab is allowed to continue, false if blocked
   */
  public init(): boolean {
    // Check if BroadcastChannel is supported
    if (typeof BroadcastChannel === 'undefined') {
      // BroadcastChannel not supported, allow the tab to continue
      console.warn(
        'BroadcastChannel API not supported, single tab enforcement disabled',
      );
      return true;
    }

    try {
      this.channel = new BroadcastChannel(this.channelName);

      // Set up message handler
      this.channel.onmessage = (event: MessageEvent<TabMessage>) => {
        this.handleMessage(event.data);
      };

      // Send a ping to detect existing tabs
      this.sendPing();
    } catch (error) {
      console.error('Failed to initialize SingleTabEnforcer:', error);
      // On error, allow the tab to continue
      return true;
    }

    return !this.isBlocked;
  }

  /**
   * Handles incoming messages from other tabs.
   */
  private handleMessage(message: TabMessage): void {
    if (message.type === 'ping') {
      // Another tab is checking if we exist, respond with pong
      this.sendPong();
    } else if (message.type === 'pong') {
      // Another tab already exists, block this one
      this.block();
    }
  }

  /**
   * Sends a ping message to detect other tabs.
   */
  private sendPing(): void {
    if (this.channel) {
      const message: TabMessage = {
        type: 'ping',
        timestamp: Date.now(),
      };
      this.channel.postMessage(message);
    }
  }

  /**
   * Sends a pong message to indicate this tab is active.
   */
  private sendPong(): void {
    if (this.channel) {
      const message: TabMessage = {
        type: 'pong',
        timestamp: Date.now(),
      };
      this.channel.postMessage(message);
    }
  }

  /**
   * Blocks this tab and invokes the onBlocked callback.
   */
  private block(): void {
    if (this.isBlocked) {
      return;
    }
    this.isBlocked = true;
    this.onBlocked(T.arenaContestMultipleTabsDetected);
  }

  /**
   * Returns whether this tab is blocked.
   */
  public get blocked(): boolean {
    return this.isBlocked;
  }

  /**
   * Cleans up resources. Should be called when leaving the page.
   */
  public destroy(): void {
    if (this.channel) {
      this.channel.close();
      this.channel = null;
    }
  }
}

/**
 * Creates and initializes a SingleTabEnforcer for a contest.
 *
 * @param contestAlias - The contest alias
 * @param onBlocked - Callback when another tab is detected
 * @returns The enforcer instance, or null if initialization failed
 */
export function enforceSingleTab(
  contestAlias: string,
  onBlocked: (message: string) => void,
): SingleTabEnforcer | null {
  const enforcer = new SingleTabEnforcer({
    contestAlias,
    onBlocked,
  });

  enforcer.init();

  // Set up cleanup on page unload
  window.addEventListener('beforeunload', () => {
    enforcer.destroy();
  });

  return enforcer;
}
