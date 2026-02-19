export class SafeStorage {
  static setItem(key: string, value: string): boolean {
    try {
      localStorage.setItem(key, value);
      return true;
    } catch (e) {
      if (e instanceof DOMException) {
        if (e.name === 'QuotaExceededError') {
          console.warn('localStorage quota exceeded');
          this.clearOldItems();
        } else if (e.name === 'SecurityError') {
          console.warn('localStorage not available (private browsing mode)');
        }
      }
      return false;
    }
  }

  static getItem(key: string): string | null {
    try {
      return localStorage.getItem(key);
    } catch (e) {
      console.warn('localStorage read failed:', e);
      return null;
    }
  }

  static clearOldItems(): void {
    const thirtyDaysAgo = Date.now() - 30 * 24 * 60 * 60 * 1000;
    try {
      for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        if (key && key.startsWith('clarification-')) {
          const timestamp = localStorage.getItem(key);
          if (timestamp && parseInt(timestamp) < thirtyDaysAgo) {
            localStorage.removeItem(key);
          }
        }
      }
    } catch (e) {
      console.warn('Failed to clear old items from localStorage:', e);
    }
  }
}
