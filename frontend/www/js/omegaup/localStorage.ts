import * as ui from './ui';
import T from './lang';

const DEFAULT_EXPIRY_DAYS = 7;

export function safeGetItem(key: string): string | null {
  try {
    return localStorage.getItem(key);
  } catch (e) {
    console.warn(`localStorage unavailable for key "${key}":`, e);
    return null;
  }
}

export function safeSetItem(
  key: string,
  value: string,
  showWarnings = true,
): boolean {
  try {
    localStorage.setItem(key, value);
    return true;
  } catch (e) {
    if (e instanceof DOMException && e.name === 'QuotaExceededError') {
      console.error('localStorage quota exceeded. Cannot save draft.');
      if (showWarnings) {
        ui.warning(T.localStorageQuotaExceeded);
      }
    } else {
      console.warn(`localStorage unavailable for key "${key}":`, e);
      if (showWarnings) {
        ui.warning(T.localStorageDraftAutosaveUnavailable);
      }
    }
    return false;
  }
}

export function isDraftExpired(
  timestamp: string | null,
  expiryDays = DEFAULT_EXPIRY_DAYS,
): boolean {
  if (!timestamp) return false;

  try {
    const savedTime = parseInt(timestamp, 10);
    if (isNaN(savedTime)) return false;

    const expiryMs = expiryDays * 24 * 60 * 60 * 1000;
    return Date.now() - savedTime > expiryMs;
  } catch (e) {
    return false;
  }
}

export function checkLocalStorageAvailability(): boolean {
  try {
    const testKey = 'omegaup:test:availability';
    localStorage.setItem(testKey, 'test');
    localStorage.removeItem(testKey);
    return true;
  } catch (e) {
    return false;
  }
}

export function clearDraft(storageKey: string, timestampKey: string): void {
  try {
    localStorage.removeItem(storageKey);
    localStorage.removeItem(timestampKey);
  } catch (e) {
    console.warn('Failed to clear draft keys from localStorage:', e);
  }
}

export function saveDraftWithTimestamp(
  storageKey: string,
  timestampKey: string,
  data: string,
  showWarnings = true,
): boolean {
  const success = safeSetItem(storageKey, data, showWarnings);
  if (success) {
    safeSetItem(timestampKey, Date.now().toString(), false);
  }
  return success;
}

export function checkDraftExists(storageKey: string): boolean {
  try {
    const stored = localStorage.getItem(storageKey);
    return stored !== null && stored !== '';
  } catch (e) {
    return false;
  }
}
