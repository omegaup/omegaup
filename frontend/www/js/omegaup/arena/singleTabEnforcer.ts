// Copyright (c) 2014-2025 omegaUp. All rights reserved.
// Use of this source code is governed by the BSD-style license found in
// the LICENSE file in the root directory of the source tree.

import T from '../lang';

/**
 * Interface for messages exchanged between tabs via BroadcastChannel.
 */
interface TabMessage {
  type: 'ping' | 'pong';
  /**
   * Unix timestamp in milliseconds. Currently not used by the enforcer logic,
   * but reserved for future features such as detecting stale messages or
   * aiding debugging of cross-tab communication.
   */
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
 *     // Show error UI using safe DOM manipulation to avoid XSS
 *     const errorDiv = document.createElement('div');
 *     errorDiv.className = 'error';
 *     errorDiv.textContent = message;
 *     document.body.innerHTML = '';
 *     document.body.appendChild(errorDiv);
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
  private beforeUnloadHandler: (() => void) | null = null;

  constructor(options: SingleTabEnforcerOptions) {
    this.contestAlias = options.contestAlias;
    this.onBlocked = options.onBlocked;
    this.channelName = `omegaup-contest-${this.contestAlias}`;
  }

  /**
   * Initializes the single tab enforcement.
   *
   * Note: This method uses async messaging via BroadcastChannel, so blocking
   * state is determined asynchronously. Callers should NOT rely on a return
   * value; instead, use the `onBlocked` callback (passed in constructor) or
   * check the `blocked` property after allowing time for the async response.
   *
   * @example
   * ```typescript
   * const enforcer = new SingleTabEnforcer({ contestAlias, onBlocked });
   * enforcer.init();
   * // Wait for potential pong response
   * await new Promise((resolve) => setTimeout(resolve, 100));
   * if (enforcer.blocked) {
   *   return; // Stop initialization
   * }
   * ```
   */
  public init(): void {
    // Check if BroadcastChannel is supported
    if (typeof BroadcastChannel === 'undefined') {
      // BroadcastChannel not supported, allow the tab to continue
      console.warn(
        'BroadcastChannel API not supported, single tab enforcement disabled',
      );
      return;
    }

    try {
      this.channel = new BroadcastChannel(this.channelName);

      // Set up message handler
      this.channel.onmessage = (event: MessageEvent<TabMessage>) => {
        this.handleMessage(event.data);
      };

      // Send a ping to detect existing tabs (response arrives asynchronously)
      this.sendPing();
    } catch (error) {
      console.error('Failed to initialize SingleTabEnforcer:', error);
      // On error, allow the tab to continue
    }
  }

  /**
   * Handles incoming messages from other tabs.
   */
  private handleMessage(message: TabMessage): void {
    if (message.type === 'ping') {
      // Only respond if this tab is not blocked - blocked tabs should not
      // claim to be active participants
      if (!this.isBlocked) {
        this.sendPong();
      }
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
   * Removes the beforeunload listener if one was registered.
   */
  public destroy(): void {
    if (this.channel) {
      this.channel.close();
      this.channel = null;
    }
    if (this.beforeUnloadHandler) {
      window.removeEventListener('beforeunload', this.beforeUnloadHandler);
      this.beforeUnloadHandler = null;
    }
  }

  /**
   * Registers a beforeunload handler for automatic cleanup.
   * The handler will be removed when destroy() is called.
   */
  public registerBeforeUnloadHandler(): void {
    // Prevent orphaning previous listeners by returning early if already registered
    if (this.beforeUnloadHandler) {
      return;
    }
    this.beforeUnloadHandler = () => {
      this.destroy();
    };
    window.addEventListener('beforeunload', this.beforeUnloadHandler);
  }
}

/**
 * Creates and initializes a SingleTabEnforcer for a contest.
 *
 * This function creates an enforcer, initializes it, and registers a
 * beforeunload handler for automatic cleanup. The blocking state is
 * determined asynchronously via the onBlocked callback.
 *
 * @param contestAlias - The contest alias
 * @param onBlocked - Callback invoked asynchronously when another tab is detected
 * @returns The enforcer instance (always returns the enforcer; use the
 *          onBlocked callback or enforcer.blocked property to check blocking state)
 */
export function enforceSingleTab(
  contestAlias: string,
  onBlocked: (message: string) => void,
): SingleTabEnforcer {
  const enforcer = new SingleTabEnforcer({
    contestAlias,
    onBlocked,
  });

  enforcer.init();
  enforcer.registerBeforeUnloadHandler();

  return enforcer;
}
