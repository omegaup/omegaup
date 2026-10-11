import { SafeStorage } from './safe_storage';

describe('SafeStorage.clearOldItems', () => {
  const now = 1_700_000_000_000;
  const oneDay = 24 * 60 * 60 * 1000;
  const old = String(now - 31 * oneDay);
  const recent = String(now - 1 * oneDay);

  beforeEach(() => {
    localStorage.clear();
    jest.spyOn(Date, 'now').mockReturnValue(now);
  });

  afterEach(() => {
    jest.restoreAllMocks();
    localStorage.clear();
  });

  it('removes all consecutive old clarification entries', () => {
    localStorage.setItem('clarification-a', old);
    localStorage.setItem('clarification-b', old);
    localStorage.setItem('clarification-c', old);

    SafeStorage.clearOldItems();

    expect(localStorage.getItem('clarification-a')).toBeNull();
    expect(localStorage.getItem('clarification-b')).toBeNull();
    expect(localStorage.getItem('clarification-c')).toBeNull();
    expect(localStorage.length).toBe(0);
  });

  it('keeps recent clarification entries and unrelated keys', () => {
    localStorage.setItem('clarification-old', old);
    localStorage.setItem('clarification-recent', recent);
    localStorage.setItem('other-key', old);

    SafeStorage.clearOldItems();

    expect(localStorage.getItem('clarification-old')).toBeNull();
    expect(localStorage.getItem('clarification-recent')).toBe(recent);
    expect(localStorage.getItem('other-key')).toBe(old);
  });

  it('removes old entries interleaved with recent ones', () => {
    localStorage.setItem('clarification-1', old);
    localStorage.setItem('clarification-2', recent);
    localStorage.setItem('clarification-3', old);
    localStorage.setItem('clarification-4', old);
    localStorage.setItem('clarification-5', recent);

    SafeStorage.clearOldItems();

    expect(localStorage.getItem('clarification-1')).toBeNull();
    expect(localStorage.getItem('clarification-2')).toBe(recent);
    expect(localStorage.getItem('clarification-3')).toBeNull();
    expect(localStorage.getItem('clarification-4')).toBeNull();
    expect(localStorage.getItem('clarification-5')).toBe(recent);
  });
});
