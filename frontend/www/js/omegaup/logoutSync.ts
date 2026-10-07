const CHANNEL_NAME = 'omegaup-logout-sync';
const EPHEMERAL_SOURCES_PREFIX = 'ephemeral-sources-';
interface LogoutSyncMessage {
  type: 'logout';
  timestamp: number;
}

export function clearSessionStorageForLogout(): void {
  for (const key of Object.keys(sessionStorage)) {
    if (key.startsWith(EPHEMERAL_SOURCES_PREFIX)) {
      continue;
    }
    sessionStorage.removeItem(key);
  }
}

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
    channel.close();
  } catch (_e) {
    // Silently fail – cross-tab sync is best-effort and must never
    // interfere with the core logout flow.
  }
}

export function initLogoutListener(onLogout: () => void): () => void {
  if (typeof BroadcastChannel === 'undefined') {
    return () => {};
  }

  let channel: BroadcastChannel | null = null;

  function cleanup(): void {
    if (channel) {
      channel.onmessage = null;
      channel.close();
      channel = null;
    }
  }

  try {
    channel = new BroadcastChannel(CHANNEL_NAME);
    channel.onmessage = (event: MessageEvent<LogoutSyncMessage>) => {
      if (event.data?.type === 'logout') {
        cleanup();
        onLogout();
      }
    };
  } catch (_e) {
    return () => {};
  }

  return cleanup;
}
