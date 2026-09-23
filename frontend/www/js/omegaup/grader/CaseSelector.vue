<template>
  <div class="case-selector" :class="theme">
    <div class="selector-header">
      <span class="header-title">
        <i class="fas fa-list-ul" aria-hidden="true"></i> Test Cases
      </span>
      <button class="icon-btn add-btn" title="Add new case" @click="addCase">
        <i class="fas fa-plus" aria-hidden="true"></i>
      </button>
    </div>

    <div class="case-list">
      <div v-if="Object.keys(cases).length === 0" class="empty-state">
        <p>No test cases available.</p>
      </div>
      <div
        v-for="(_, caseName) in cases"
        :key="caseName"
        class="case-item"
        :class="{ 'case-item--active': currentCase === caseName }"
        role="button"
        :tabindex="0"
        @click="selectCase(caseName)"
        @keydown.enter="selectCase(caseName)"
        @keydown.space.prevent="selectCase(caseName)"
      >
        <div class="case-info">
          <i class="fas fa-vial case-icon" aria-hidden="true"></i>
          <span class="case-name" :title="caseName">{{ caseName }}</span>
        </div>

        <button
          v-if="caseName !== 'sample'"
          class="icon-btn delete-btn"
          title="Delete case"
          aria-label="Delete case"
          @click.stop="deleteCase(caseName)"
        >
          <i class="fas fa-trash-alt" aria-hidden="true"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import store from './GraderStore';
import T from '../lang';

@Component
export default class CaseSelector extends Vue {
  T = T;

  get theme(): string {
    return store.getters['theme'];
  }

  get cases(): Record<string, unknown> {
    return store.getters['inputCases'] || {};
  }

  get currentCase(): string {
    return store.getters['currentCase'];
  }

  selectCase(caseName: string): void {
    if (this.currentCase !== caseName) {
      store.dispatch('currentCase', caseName);
    }
  }

  addCase(): void {
    const name = prompt('Enter a name for the new test case:');
    if (!name || name.trim() === '') return;

    const cleanName = name.trim();
    if (this.cases[cleanName]) {
      alert('A test case with this name already exists!');
      return;
    }

    store.dispatch('createCase', {
      name: cleanName,
      in: '',
      out: '',
      weight: 1,
    });
    this.selectCase(cleanName);
  }

  deleteCase(caseName: string): void {
    if (
      confirm(`Are you sure you want to delete the test case '${caseName}'?`)
    ) {
      store.dispatch('removeCase', caseName);
    }
  }
}
</script>

<style lang="scss" scoped>
.case-selector {
  display: flex;
  flex-direction: column;
  height: 100%;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: #f9fafb;
  border-left: 1px solid #e5e7eb;

  .vs-dark & {
    background: #252525;
    border-left-color: #333;
  }
}

.selector-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 16px;
  background: #fff;
  border-bottom: 1px solid #e5e7eb;
  min-height: 44px;

  .vs-dark & {
    background: #1e1e1e;
    border-bottom-color: #333;
  }
}

.header-title {
  font-size: 13px;
  font-weight: 600;
  color: #4b5563;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  display: flex;
  align-items: center;
  gap: 8px;

  i {
    color: #9ca3af;
  }

  .vs-dark & {
    color: #9ca3af;
    i {
      color: #6b7280;
    }
  }
}

.icon-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border: none;
  background: transparent;
  color: #6b7280;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.15s;

  &:hover {
    background: #e5e7eb;
    color: #1f2937;
  }

  &:focus-visible {
    outline: 2px solid #3b82f6;
  }

  .vs-dark & {
    color: #9ca3af;
    &:hover {
      background: #404040;
      color: #f3f4f6;
    }
  }
}

.add-btn {
  color: #3b82f6;
  background: rgba(59, 130, 246, 0.1);

  &:hover {
    background: rgba(59, 130, 246, 0.2);
    color: #2563eb;
  }

  .vs-dark & {
    color: #60a5fa;
    background: rgba(96, 165, 250, 0.1);
    &:hover {
      background: rgba(96, 165, 250, 0.2);
      color: #93c5fd;
    }
  }
}

.case-list {
  flex: 1;
  overflow-y: auto;
  padding: 8px 0;
}

.empty-state {
  padding: 16px;
  text-align: center;
  font-size: 13px;
  color: #9ca3af;
  font-style: italic;

  .vs-dark & {
    color: #6b7280;
  }
}

.case-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 16px;
  cursor: pointer;
  border-left: 3px solid transparent;
  transition: background 0.15s;

  &:hover {
    background: #e5e7eb;
  }

  &.case-item--active {
    background: #eff6ff;
    border-left-color: #3b82f6;

    .case-name {
      color: #1d4ed8;
      font-weight: 600;
    }

    .case-icon {
      color: #3b82f6;
    }
  }

  .vs-dark & {
    &:hover {
      background: #333;
    }

    &.case-item--active {
      background: rgba(59, 130, 246, 0.15);
      border-left-color: #60a5fa;

      .case-name {
        color: #93c5fd;
      }

      .case-icon {
        color: #60a5fa;
      }
    }
  }
}

.case-info {
  display: flex;
  align-items: center;
  gap: 10px;
  overflow: hidden;
}

.case-icon {
  font-size: 14px;
  color: #9ca3af;

  .vs-dark & {
    color: #6b7280;
  }
}

.case-name {
  font-size: 13px;
  color: #4b5563;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;

  .vs-dark & {
    color: #d1d5db;
  }
}

.delete-btn {
  opacity: 0;
  width: 24px;
  height: 24px;

  .case-item:hover & {
    opacity: 1;
  }

  &:hover {
    color: #dc2626;
    background: rgba(220, 38, 38, 0.1);
  }

  .vs-dark &:hover {
    color: #f87171;
    background: rgba(248, 113, 113, 0.15);
  }
}
</style>
