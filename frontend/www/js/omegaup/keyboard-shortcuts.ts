export interface Shortcut {
  key: string;
  ctrlKey?: boolean;
  altKey?: boolean;
  shiftKey?: boolean;
  metaKey?: boolean;
  action: () => void;
  description: string;
}

export class KeyboardShortcutManager {
  private shortcuts: Shortcut[] = [];
  private static instance: KeyboardShortcutManager;

  private constructor() {
    this.handleKeyDown = this.handleKeyDown.bind(this);
    document.addEventListener('keydown', this.handleKeyDown);
  }

  static getInstance(): KeyboardShortcutManager {
    if (!KeyboardShortcutManager.instance) {
      KeyboardShortcutManager.instance = new KeyboardShortcutManager();
    }
    return KeyboardShortcutManager.instance;
  }

  registerShortcut(shortcut: Shortcut): void {
    this.shortcuts.push(shortcut);
  }

  unregisterShortcut(key: string, ctrlKey = false, altKey = false, shiftKey = false, metaKey = false): void {
    this.shortcuts = this.shortcuts.filter(s => 
      s.key !== key || 
      !!s.ctrlKey !== ctrlKey || 
      !!s.altKey !== altKey || 
      !!s.shiftKey !== shiftKey || 
      !!s.metaKey !== metaKey
    );
  }

  getShortcuts(): Shortcut[] {
    return this.shortcuts;
  }

  private handleKeyDown(event: KeyboardEvent): void {
    if (
      event.target instanceof HTMLInputElement ||
      event.target instanceof HTMLTextAreaElement ||
      (event.target instanceof HTMLElement && event.target.isContentEditable)
    ) {
      if (event.key === 'Escape') {
         // Let Escape pass through to close modals even from inputs, or handle specific logic
      } else {
         return;
      }
    }

    const matchedShortcut = [...this.shortcuts].reverse().find((s) => {
      const keyMatch = s.key.toLowerCase() === event.key.toLowerCase();
      const ctrlMatch = !!s.ctrlKey === event.ctrlKey;
      const altMatch = !!s.altKey === event.altKey;
      const shiftMatch = !!s.shiftKey === event.shiftKey;
      const metaMatch = !!s.metaKey === event.metaKey;

      return keyMatch && ctrlMatch && altMatch && shiftMatch && metaMatch;
    });

    if (matchedShortcut) {
      console.log('KeyboardShortcutManager: Matched shortcut', matchedShortcut);
      event.preventDefault();
      matchedShortcut.action();
    }
  }
}

export const shortcutManager = KeyboardShortcutManager.getInstance();
