<template>
  <div v-if="show" class="keyboard-shortcuts-help-overlay" @click.self="close">
    <div class="keyboard-shortcuts-help-modal">
      <div class="modal-header">
        <h3>{{ T.keyboardShortcutsTitle }}</h3>
        <button class="close-button" @click="close">&times;</button>
      </div>
      <div class="modal-body">
        <ul class="shortcuts-list">
          <li
            v-for="(shortcut, index) in shortcuts"
            :key="index"
            class="shortcut-item"
          >
            <span class="shortcut-keys">
              <span v-if="shortcut.ctrlKey">Ctrl + </span>
              <span v-if="shortcut.altKey">Alt + </span>
              <span v-if="shortcut.shiftKey">Shift + </span>
              <span v-if="shortcut.metaKey">Meta + </span>
              <span class="key">{{ shortcut.key.toUpperCase() }}</span>
            </span>
            <span class="shortcut-description">{{ shortcut.description }}</span>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import { shortcutManager, Shortcut } from '../../keyboard-shortcuts';
import T from '../../lang';

@Component
export default class KeyboardShortcutsHelp extends Vue {
  show = false;
  T = T;

  get shortcuts(): Shortcut[] {
    const allShortcuts = shortcutManager.getShortcuts();
    const uniqueShortcuts: Shortcut[] = [];
    const seen = new Set<string>();
    for (let i = allShortcuts.length - 1; i >= 0; i--) {
      const s = allShortcuts[i];
      const key = `${s.key}-${s.ctrlKey}-${s.altKey}-${s.shiftKey}-${s.metaKey}`;
      if (!seen.has(key)) {
        uniqueShortcuts.unshift(s);
        seen.add(key);
      }
    }
    return uniqueShortcuts;
  }

  mounted() {
    shortcutManager.registerShortcut({
      key: '/',
      ctrlKey: true,
      description: T.keyboardShortcutsHelp,
      action: () => {
        this.show = !this.show;
      },
    });

    shortcutManager.registerShortcut({
      key: 'Escape',
      description: T.keyboardShortcutsCloseModal,
      action: () => {
        if (this.show) {
          this.close();
        }
      },
    });
  }

  close() {
    this.show = false;
  }
}
</script>

<style scoped>
.keyboard-shortcuts-help-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 10000;
}

.keyboard-shortcuts-help-modal {
  background: white;
  padding: 20px;
  border-radius: 8px;
  width: 400px;
  max-width: 90%;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.modal-header h3 {
  margin: 0;
}

.close-button {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
}

.shortcuts-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.shortcut-item {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  border-bottom: 1px solid #eee;
}

.shortcut-item:last-child {
  border-bottom: none;
}

.shortcut-keys {
  font-family: monospace;
  font-weight: bold;
  background: #f5f5f5;
  padding: 2px 6px;
  border-radius: 4px;
}
</style>
