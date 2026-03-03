// Copyright (c) 2014-2025 omegaUp. All rights reserved.
// Use of this source code is governed by the BSD-style license found in
// the LICENSE file in the root directory of the source tree.

/**
 * Cross-tab logout synchronisation using the BroadcastChannel API.
 *
 * When a user logs out in one browser tab the server invalidates the session
 * cookie immediately, but other open tabs continue to show the authenticated
 * UI until they reload or an API call fails.  This module solves that by
 * broadcasting a lightweight "logout" message to every same-origin tab at the
 * moment the logout page runs, so those tabs can redirect the user to the
 * home page right away.
 *
 * Design notes
 * ------------
 * - Authentication state is held in memory by `OmegaUp` (see omegaup.ts) and
 *   is initialised on each page-load via `api.Session.currentSession()`.
 *   There is no client-side persistent token/JWT in localStorage.  The source
 *   of truth for "is the session valid?" is the server-side session cookie.
 * - Because a logout invalidates the cookie immediately, any subsequent call
 *   to `/api/session/currentSession/` by another tab will return
 *   `session.valid = false`.  The redirect performed by `initLogoutListener`'s
 *   `onLogout` callback achieves the same outcome faster and without an
 *   unnecessary round-trip.
 * - The channel is fire-and-close on the sender side and self-closing on the
 *   receiver side after the first valid message, preventing duplicate
 *   redirects.
 */

/** Channel name shared by all instances across the same origin. */
const CHANNEL_NAME = 'omegaup-logout-sync';

/**
 * Message shape exchanged between tabs through the logout sync channel.
 */
interface LogoutSyncMessage {
  type: 'logout';
  /**
   * Unix timestamp in milliseconds.  Kept for debugging and
   * potential future use (e.g. ignoring stale messages).
   */
  timestamp: number;
}

/**
 * Broadcasts a logout event to all other open tabs of the same origin.
 *
 * Uses fire-and-close semantics: opens the channel, posts the message, then
 * immediately closes the channel to free resources.  This function is
 * intentionally synchronous and best-effort – a failure here must never
 * prevent the normal logout redirect from proceeding.
 *
 * Call this from the logout page *before* performing any redirect.
 */
export function broadcastLogout(): void {
  if (typeof BroadcastChannel === 'undefined') {
    return;
  }
  try {
    const channel = new BroadcastChannel(CHANNEL_NAME);
    const message: LogoutSyncMessage = {
      type: 'logout',
      timestamp: Date.now(),
    };
    channel.postMessage(message);
    // Close immediately; the message has been queued by the browser.
    channel.close();
  } catch (_e) {
    // Silently fail – cross-tab sync is best-effort and must never
    // interfere with the core logout flow.
  }
}

/**
 * Subscribes to logout broadcast events from other tabs.
 *
 * The `onLogout` callback is invoked **at most once**: on the first valid
 * logout message received from another tab.  The channel is closed
 * immediately afterwards to prevent duplicate invocations.
 *
 * This function should be called only when the user is confirmed to be
 * logged in (e.g. after `api.Session.currentSession()` returns
 * `session.valid = true`).
 *
 * @param onLogout - Callback executed when a logout event is received from
 *   another tab.  Typically redirects to the home/login page.
 * @returns A cleanup function.  Call it when the page is unloaded or the
 *   listener is no longer needed to avoid resource leaks.
 */
export function initLogoutListener(onLogout: () => void): () => void {
  if (typeof BroadcastChannel === 'undefined') {
    // BroadcastChannel is not available in this environment (e.g. some older
    // browsers).  Return a no-op cleanup function.
    return () => {};
  }

  let channel: BroadcastChannel | null = null;

  function cleanup(): void {
    if (channel) {
      channel.close();
      channel = null;
    }
  }

  try {
    channel = new BroadcastChannel(CHANNEL_NAME);
    channel.onmessage = (event: MessageEvent<LogoutSyncMessage>) => {
      if (event.data?.type === 'logout') {
        // Close the channel before calling onLogout to avoid processing
        // any further messages (prevents duplicate redirects).
        cleanup();
        onLogout();
      }
    };
  } catch (_e) {
    // Failed to open the channel; return a no-op cleanup.
    return () => {};
  }

  return cleanup;
}
