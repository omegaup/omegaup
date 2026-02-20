// Centralized constants for grader components
export const EDITOR = {
  FONT_SIZES: [12, 13, 14, 16, 18, 20] as const,
  DEFAULT_FONT_SIZE: 13,
} as const;

export const TIMING = {
  COPY_FEEDBACK_DURATION_MS: 2000,
  DEBOUNCE_EDITOR_CHANGE_MS: 300,
} as const;

export const KEYBOARD_SHORTCUTS = {
  RESET: 'Ctrl+Shift+R',
  FULLSCREEN: 'F11',
  EXIT_FULLSCREEN: 'Escape',
} as const;

export const LIMITS = {
  MAX_HISTORY_SIZE: 50,
} as const;

export type EditorConstants = typeof EDITOR;
export type TimingConstants = typeof TIMING;
export type KeyboardShortcuts = typeof KEYBOARD_SHORTCUTS;