<template>
  <div
    class="case-selector"
    :class="theme"
    role="region"
    aria-label="Test cases"
  >
    <!-- Header with summary -->
    <div class="case-header">
      <div class="header-left">
        <span class="header-title">Test Cases</span>
        <span v-if="groups && groups.length > 0" class="case-count">{{
          totalCaseCount
        }}</span>
      </div>
      <div v-if="summaryVerdict" class="header-right">
        <span
          class="summary-badge"
          :class="summaryClass"
          role="status"
          :aria-label="`Overall verdict: ${summaryVerdict}`"
        >
          <span class="verdict-dot" aria-hidden="true"></span>
          <span class="verdict-text">{{ summaryVerdict }}</span>
          <span class="sr-only">{{ verdictScreenReaderText }}</span>
        </span>
        <span class="summary-score" aria-label="Overall score">{{
          summaryScore
        }}</span>
      </div>
    </div>

    <!-- Add case form -->
    <div class="add-case-section">
      <form
        class="add-case-form"
        aria-label="Add test case form"
        @submit.prevent="createCase()"
      >
        <div class="form-row">
          <div class="input-group">
            <label for="case-weight" class="input-label">Weight</label>
            <input
              v-model.number="newCaseWeight"
              data-testid="case-weight"
              class="input-weight"
              type="number"
              placeholder="1.0"
              min="0"
              step="0.1"
              aria-describedby="weight-help"
            />
            <span class="sr-only" aria-label="Weight for scoring this test case"
              >Weight for scoring this test case</span
            >
          </div>
          <div class="input-group input-group--flex">
            <label for="case-name" class="input-label">Case Name</label>
            <input
              ref="caseNameInput"
              v-model="newCaseName"
              data-testid="case-name"
              class="input-name"
              type="text"
              placeholder="e.g., sample, edge-case-1"
              data-case-name
              aria-describedby="name-help"
              @keydown="handleInputKeydown"
            />
            <span class="sr-only" aria-label="Name for the test case"
              >Name for the test case</span
            >
          </div>
          <button
            class="btn-add"
            type="submit"
            :disabled="!canAddCase"
            data-add-button
            :aria-label="addButtonLabel"
          >
            <svg
              width="16"
              height="16"
              viewBox="0 0 16 16"
              fill="currentColor"
              aria-hidden="true"
            >
              <path
                d="M8 1a1 1 0 011 1v5h5a1 1 0 110 2H9v5a1 1 0 11-2 0V9H2a1 1 0 110-2h5V2a1 1 0 011-1z"
              />
            </svg>
            <span class="sr-only">Add test case</span>
          </button>
        </div>
        <p v-if="addCaseError" class="error-message" role="alert">
          {{ addCaseError }}
        </p>
      </form>
    </div>

    <!-- Cases list -->
    <div
      ref="casesList"
      class="cases-list"
      role="list"
      :aria-label="`${totalCaseCount} available`"
      tabindex="0"
      @keydown="handleListKeydown"
    >
      <div
        v-if="!groups || groups.length === 0"
        class="empty-state"
        role="status"
      >
        <svg
          width="56"
          height="56"
          viewBox="0 0 56 56"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          aria-hidden="true"
        >
          <rect x="12" y="12" width="32" height="32" rx="4" />
          <line x1="20" y1="24" x2="36" y2="24" />
          <line x1="20" y1="32" x2="32" y2="32" />
        </svg>
        <p class="empty-title">No test cases yet</p>
        <p class="empty-subtitle">Add your first test case above</p>
      </div>

      <template v-else>
        <div
          v-for="(group, groupIndex) in groups"
          :key="group.name"
          class="group-container"
        >
          <!-- Group header (if explicit) -->
          <div
            v-if="group.explicit"
            class="group-header"
            role="group"
            :aria-label="`Group: ${group.name}`"
          >
            <span
              class="verdict-icon"
              :class="verdictIconClass(groupResult(group.name))"
              aria-hidden="true"
            >
              {{ verdictLabel(groupResult(group.name)) }}
            </span>
            <span class="group-name">{{ group.name }}</span>
            <span class="group-score" aria-label="Group score">{{
              score(groupResult(group.name))
            }}</span>
          </div>

          <!-- Cases in group -->
          <button
            v-for="(item, itemIndex) in group.cases"
            :key="item.name"
            :ref="`case-${item.name}`"
            class="case-item"
            :class="{
              'case-item--grouped': group.explicit,
              'case-item--active': currentCase === item.name,
            }"
            type="button"
            role="listitem"
            :aria-label="getCaseAriaLabel(item)"
            :aria-current="currentCase === item.name ? 'true' : 'false'"
            :data-case-name="item.name"
            :data-group-index="groupIndex"
            :data-item-index="itemIndex"
            @click="selectCase(item.name)"
            @keydown="
              handleCaseKeydown($event, item.name, groupIndex, itemIndex)
            "
          >
            <div class="case-item-left">
              <span
                class="verdict-icon"
                :class="verdictIconClass(caseResult(item.name))"
                aria-hidden="true"
              >
                {{ verdictLabel(caseResult(item.name)) }}
              </span>
              <span class="case-name">{{ item.name }}</span>
              <span
                v-if="item.weight && item.weight !== 1"
                class="case-weight"
                aria-label="`Weight ${formatWeight(item.weight)}`"
              >
                ×{{ formatWeight(item.weight) }}
              </span>
            </div>
            <div class="case-item-right">
              <span
                class="case-score"
                :aria-label="`Score: ${score(caseResult(item.name))}`"
              >
                {{ score(caseResult(item.name)) }}
              </span>
              <button
                v-if="canRemoveCase"
                class="btn-remove"
                type="button"
                :aria-label="`Remove test case ${item.name}`"
                :title="`Remove test case ${item.name}`"
                @click.prevent.stop="confirmRemoveCase(item.name)"
              >
                <svg
                  width="14"
                  height="14"
                  viewBox="0 0 14 14"
                  fill="currentColor"
                  aria-hidden="true"
                >
                  <path
                    d="M7 5.586L11.293 1.293a1 1 0 111.414 1.414L8.414 7l4.293 4.293a1 1 0 01-1.414 1.414L7 8.414l-4.293 4.293a1 1 0 01-1.414-1.414L5.586 7 1.293 2.707A1 1 0 012.707 1.293L7 5.586z"
                  />
                </svg>
              </button>
            </div>
          </button>
        </div>
      </template>
    </div>

    <!-- Loading indicator -->
    <div
      v-if="isLoading"
      class="loading-overlay"
      role="status"
      aria-live="polite"
    >
      <div class="spinner"></div>
      <span class="sr-only">Loading test cases...</span>
    </div>

    <!-- Delete confirmation modal -->
    <div
      v-if="showDeleteModal"
      class="modal-overlay"
      @click.self="showDeleteModal = false"
    >
      <div
        class="modal-content"
        role="dialog"
        aria-labelledby="delete-modal-title"
        aria-modal="true"
      >
        <h3 aria-label="Remove Test Case">Remove Test Case</h3>
        <p>
          Are you sure you want to remove the test case
          <strong>{{ caseToDelete }}</strong
          >?
        </p>
        <div class="modal-actions">
          <button class="btn btn-secondary" @click="showDeleteModal = false">
            Cancel
          </button>
          <button class="btn btn-danger" @click="removeCase(caseToDelete)">
            Remove
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import store from './GraderStore';
import { types } from '../api_types';
import { GraderResults, CaseSelectorGroup } from './GraderStore';
import T from '../lang';

// Constants
const CASE_NAME_MAX_LENGTH = 50;
const CASE_NAME_PATTERN = /^[a-zA-Z0-9_-]+$/;

@Component
export default class CaseSelector extends Vue {
  newCaseWeight: null | number = null;
  newCaseName: string = '';
  addCaseError: string = '';
  isLoading: boolean = false;
  showDeleteModal: boolean = false;
  caseToDelete: string = '';

  readonly T = T;

  // Computed
  get theme(): string {
    return store.getters['theme'];
  }

  get summaryVerdict(): string {
    if (!store.state.results || !store.state.results.verdict) return '';
    return store.state.results.verdict;
  }

  get summaryScore(): string {
    if (!store.state.results || !store.state.results.verdict) return '';
    return this.score(store.state.results);
  }

  get summaryClass(): string {
    const v = this.summaryVerdict;
    if (v === 'AC') return 'verdict-ac';
    if (v === 'PA') return 'verdict-pa';
    if (v) return 'verdict-wa';
    return '';
  }

  get verdictScreenReaderText(): string {
    const verdictMap: Record<string, string> = {
      AC: 'All test cases passed',
      PA: 'Some test cases passed',
      WA: 'Some test cases failed',
      CE: 'Compilation error',
      TLE: 'Time limit exceeded',
    };
    return verdictMap[this.summaryVerdict] || this.summaryVerdict;
  }

  get groups(): CaseSelectorGroup[] {
    return store.getters['caseSelectorGroups'];
  }

  get totalCaseCount(): string {
    if (!this.groups) return '0 cases';
    const count = this.groups.reduce(
      (sum, group) => sum + group.cases.length,
      0,
    );
    return `${count} ${count === 1 ? 'case' : 'cases'}`;
  }

  get currentCase(): string {
    return store.getters['currentCase'];
  }

  set currentCase(value: string) {
    store.dispatch('currentCase', value);
  }

  get canAddCase(): boolean {
    return (
      this.newCaseName.length > 0 &&
      this.newCaseName.length <= CASE_NAME_MAX_LENGTH
    );
  }

  get addButtonLabel(): string {
    if (!this.canAddCase) return 'Enter a case name to add';
    return `Add test case ${this.newCaseName}`;
  }

  get canRemoveCase(): boolean {
    if (!this.groups) return false;
    const totalCases = this.groups.reduce((sum, g) => sum + g.cases.length, 0);
    return totalCases > 1;
  }

  // Methods
  caseResult(caseName: string): null | types.CaseResult {
    const flatCaseResults = store.getters.flatCaseResults;
    if (!flatCaseResults[caseName]) return null;
    return flatCaseResults[caseName];
  }

  groupResult(groupName: string): null | types.RunDetailsGroup {
    const results = store.state.results;
    if (!results || !results.groups) return null;
    for (const group of results.groups) {
      if (group.group == groupName) return group;
    }
    return null;
  }

  verdictLabel(
    result: null | types.RunDetailsGroup | types.CaseResult,
  ): string {
    if (!result) return '○';
    if (typeof result.verdict === 'undefined') {
      if (result.contest_score == result.max_score) return '✓';
      return '✗';
    }
    switch (result.verdict) {
      case 'CE':
        return '○';
      case 'AC':
        return '✓';
      case 'PA':
        return '½';
      case 'WA':
        return '✗';
      case 'TLE':
        return '⌚';
    }
    return '○';
  }

  verdictIconClass(
    result: null | types.RunDetailsGroup | types.CaseResult,
  ): string {
    if (!result) return 'verdict-pending';
    const v =
      typeof result.verdict !== 'undefined'
        ? result.verdict
        : result.contest_score == result.max_score
        ? 'AC'
        : 'WA';
    if (v === 'AC') return 'verdict-ac';
    if (v === 'PA') return 'verdict-pa';
    if (v === 'CE') return 'verdict-pending';
    if (v === 'TLE') return 'verdict-tle';
    return 'verdict-wa';
  }

  score(
    result: null | types.RunDetailsGroup | types.CaseResult | GraderResults,
  ): string {
    if (!result) return '—';
    const score = this.formatNumber(result.contest_score);
    const max = this.formatNumber(result.max_score || 0);
    return `${score}/${max}`;
  }

  formatNumber(value: number): string {
    const str = value.toFixed(2);
    if (str.endsWith('.00')) return str.substring(0, str.length - 3);
    return str;
  }

  formatWeight(weight: number): string {
    return weight % 1 === 0 ? weight.toString() : weight.toFixed(1);
  }

  getCaseAriaLabel(item: { name: string; weight?: number }): string {
    const result = this.caseResult(item.name);
    const verdict = result ? this.verdictLabel(result) : 'Not run';
    const scoreText = this.score(result);
    const weightText =
      item.weight && item.weight !== 1
        ? `, weight ${this.formatWeight(item.weight)}`
        : '';
    return `Test case ${item.name}${weightText}, ${verdict}, score ${scoreText}`;
  }

  selectCase(name: string): void {
    this.currentCase = name;
    this.announceToScreenReader(`Selected test case ${name}`);
  }

  validateCaseName(name: string): string | null {
    if (!name) return 'Case name is required';
    if (name.length > CASE_NAME_MAX_LENGTH)
      return `Case name must be ${CASE_NAME_MAX_LENGTH} characters or less`;
    if (!CASE_NAME_PATTERN.test(name))
      return 'Case name can only contain letters, numbers, hyphens, and underscores';

    // Check for duplicates

    const existingCases = ([] as {
      name: string;
      item: { in: string; out: string; weight?: number };
    }[]).concat(...this.groups.map((g) => g.cases));
    if (existingCases.some((c) => c.name === name)) {
      return 'A test case with this name already exists';
    }

    return null;
  }

  createCase(): void {
    this.addCaseError = '';

    const error = this.validateCaseName(this.newCaseName);
    if (error) {
      this.addCaseError = error;
      return;
    }

    this.isLoading = true;

    store
      .dispatch('createCase', {
        name: this.newCaseName,
        weight: this.newCaseWeight ?? 1,
      })
      .then(() => {
        this.announceToScreenReader(`Test case ${this.newCaseName} added`);
        this.newCaseWeight = null;
        this.newCaseName = '';
        this.addCaseError = '';

        // Focus the input for quick adding
        this.$nextTick(() => {
          (this.$refs.caseNameInput as HTMLInputElement)?.focus();
        });
      })
      .catch((err: Error) => {
        this.addCaseError = `Failed to add case: ${err.message}`;
      })
      .finally(() => {
        this.isLoading = false;
      });
  }

  confirmRemoveCase(name: string): void {
    this.caseToDelete = name;
    this.showDeleteModal = true;
  }

  removeCase(name: string): void {
    this.isLoading = true;

    store
      .dispatch('removeCase', name)
      .then(() => {
        this.announceToScreenReader(`Test case ${name} removed`);
        this.showDeleteModal = false;
        this.caseToDelete = '';
      })
      .catch((err: Error) => {
        console.error('Failed to remove case:', err);
        this.addCaseError = `Failed to remove case: ${err.message}`;
      })
      .finally(() => {
        this.isLoading = false;
      });
  }

  // Keyboard navigation
  handleInputKeydown(e: KeyboardEvent): void {
    if (e.key === 'Escape') {
      this.newCaseName = '';
      (e.target as HTMLInputElement).blur();
    }
  }

  handleListKeydown(e: KeyboardEvent): void {
    const focusedElement = document.activeElement;
    if (!focusedElement || !focusedElement.hasAttribute('data-case-name'))
      return;

    const currentName = focusedElement.getAttribute('data-case-name');
    const groupIndex = parseInt(
      focusedElement.getAttribute('data-group-index') || '0',
    );
    const itemIndex = parseInt(
      focusedElement.getAttribute('data-item-index') || '0',
    );

    let nextElement: Element | null = null;

    switch (e.key) {
      case 'ArrowDown':
        e.preventDefault();
        nextElement = this.getNextCase(groupIndex, itemIndex);
        break;
      case 'ArrowUp':
        e.preventDefault();
        nextElement = this.getPreviousCase(groupIndex, itemIndex);
        break;
      case 'Home':
        e.preventDefault();
        nextElement = this.getFirstCase();
        break;
      case 'End':
        e.preventDefault();
        nextElement = this.getLastCase();
        break;
      case 'Enter':
      case ' ':
        e.preventDefault();
        if (currentName) this.selectCase(currentName);
        break;
      case 'Delete':
        if (this.canRemoveCase && currentName) {
          e.preventDefault();
          this.confirmRemoveCase(currentName);
        }
        break;
    }

    if (nextElement instanceof HTMLElement) {
      nextElement.focus();
    }
  }

  handleCaseKeydown(): void {
    // Handled by list keydown
  }

  getNextCase(groupIndex: number, itemIndex: number): Element | null {
    if (!this.groups || this.groups.length === 0) return null;

    const currentGroup = this.groups[groupIndex];
    if (itemIndex < currentGroup.cases.length - 1) {
      // Next in same group
      const nextCase = currentGroup.cases[itemIndex + 1];
      return this.getCaseElement(nextCase.name);
    } else if (groupIndex < this.groups.length - 1) {
      // First in next group
      const nextGroup = this.groups[groupIndex + 1];
      return this.getCaseElement(nextGroup.cases[0].name);
    }
    return null;
  }

  getPreviousCase(groupIndex: number, itemIndex: number): Element | null {
    if (!this.groups || this.groups.length === 0) return null;

    if (itemIndex > 0) {
      // Previous in same group
      const currentGroup = this.groups[groupIndex];
      const prevCase = currentGroup.cases[itemIndex - 1];
      return this.getCaseElement(prevCase.name);
    } else if (groupIndex > 0) {
      // Last in previous group
      const prevGroup = this.groups[groupIndex - 1];
      const lastCase = prevGroup.cases[prevGroup.cases.length - 1];
      return this.getCaseElement(lastCase.name);
    }
    return null;
  }

  getFirstCase(): Element | null {
    if (!this.groups || this.groups.length === 0) return null;
    return this.getCaseElement(this.groups[0].cases[0].name);
  }

  getLastCase(): Element | null {
    if (!this.groups || this.groups.length === 0) return null;
    const lastGroup = this.groups[this.groups.length - 1];
    const lastCase = lastGroup.cases[lastGroup.cases.length - 1];
    return this.getCaseElement(lastCase.name);
  }

  getCaseElement(name: string): Element | null {
    return this.$el.querySelector(`[data-case-name="${name}"]`);
  }

  announceToScreenReader(message: string): void {
    const announcement = document.createElement('div');
    announcement.setAttribute('role', 'status');
    announcement.setAttribute('aria-live', 'polite');
    announcement.className = 'sr-only';
    announcement.textContent = message;
    document.body.appendChild(announcement);

    setTimeout(() => {
      document.body.removeChild(announcement);
    }, 1000);
  }
}
</script>

<style lang="scss" scoped>
.case-selector {
  display: flex;
  flex-direction: column;
  height: 100%;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  font-size: 13px;
  background: #fff;
  color: #1f2937;
  position: relative;

  &.vs-dark {
    background: #1e1e1e;
    color: #d4d4d4;
  }
}

/* Screen reader only */
.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border-width: 0;
}

/* Header */
.case-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 16px;
  border-bottom: 1px solid #e5e7eb;
  min-height: 44px;
  background: #fafafa;

  .vs-dark & {
    border-bottom-color: #333;
    background: #262626;
  }
}

.header-left {
  display: flex;
  align-items: center;
  gap: 10px;
}

.header-title {
  font-size: 13px;
  font-weight: 700;
  color: #1f2937;
  letter-spacing: -0.01em;

  .vs-dark & {
    color: #f3f4f6;
  }
}

.case-count {
  font-size: 11px;
  font-weight: 600;
  color: #6b7280;
  padding: 3px 8px;
  background: #f3f4f6;
  border-radius: 12px;

  .vs-dark & {
    color: #9ca3af;
    background: #374151;
  }
}

.header-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.summary-badge {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  font-weight: 700;
  padding: 5px 12px;
  border-radius: 6px;
  letter-spacing: 0.02em;

  .verdict-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
  }

  &.verdict-ac {
    color: #059669;
    background: rgba(16, 185, 129, 0.12);

    .vs-dark & {
      color: #34d399;
      background: rgba(52, 211, 153, 0.15);
    }
  }

  &.verdict-pa {
    color: #d97706;
    background: rgba(245, 158, 11, 0.12);

    .vs-dark & {
      color: #fbbf24;
      background: rgba(251, 191, 36, 0.15);
    }
  }

  &.verdict-wa {
    color: #dc2626;
    background: rgba(220, 38, 38, 0.1);

    .vs-dark & {
      color: #f87171;
      background: rgba(248, 113, 113, 0.12);
    }
  }
}

.summary-score {
  font-size: 12px;
  font-weight: 600;
  color: #6b7280;

  .vs-dark & {
    color: #9ca3af;
  }
}

/* Add case section */
.add-case-section {
  padding: 12px 16px;
  border-bottom: 1px solid #e5e7eb;
  background: #fff;

  .vs-dark & {
    border-bottom-color: #333;
    background: #1e1e1e;
  }
}

.form-row {
  display: flex;
  gap: 8px;
  align-items: flex-end;
}

.input-group {
  display: flex;
  flex-direction: column;
  gap: 4px;

  &.input-group--flex {
    flex: 1;
  }
}

.input-label {
  font-size: 11px;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  margin: 0;

  .vs-dark & {
    color: #9ca3af;
  }
}

.input-weight,
.input-name {
  border: 1px solid #d1d5db;
  border-radius: 6px;
  padding: 7px 10px;
  font-size: 13px;
  outline: none;
  transition: all 0.15s;
  background: #fff;
  color: #1f2937;

  &::placeholder {
    color: #9ca3af;
  }

  &:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }

  .vs-dark & {
    background: #262626;
    border-color: #404040;
    color: #d4d4d4;

    &:focus {
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }
  }
}

.input-weight {
  width: 80px;
  flex-shrink: 0;
  text-align: center;
}

.input-name {
  min-width: 0;
}

.btn-add {
  width: 38px;
  height: 38px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  border-radius: 6px;
  background: #3b82f6;
  color: #fff;
  cursor: pointer;
  transition: all 0.15s;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);

  &:hover:not(:disabled) {
    background: #2563eb;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
  }

  &:active:not(:disabled) {
    transform: translateY(0);
  }

  &:focus-visible {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
  }

  &:disabled {
    opacity: 0.4;
    cursor: not-allowed;
  }

  .vs-dark & {
    background: #2563eb;

    &:hover:not(:disabled) {
      background: #1d4ed8;
    }
  }
}

.error-message {
  margin: 8px 0 0 0;
  padding: 6px 10px;
  background: rgba(220, 38, 38, 0.1);
  border-left: 3px solid #dc2626;
  border-radius: 4px;
  font-size: 12px;
  color: #dc2626;

  .vs-dark & {
    background: rgba(248, 113, 113, 0.12);
    color: #f87171;
  }
}

/* Cases list */
.cases-list {
  flex: 1;
  overflow-y: auto;
  padding: 8px 0;

  &:focus {
    outline: 2px solid #3b82f6;
    outline-offset: -2px;
  }
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 64px 24px;
  color: #9ca3af;

  svg {
    margin-bottom: 16px;
    opacity: 0.4;
  }

  .empty-title {
    margin: 0 0 4px 0;
    font-size: 14px;
    font-weight: 600;
    color: #6b7280;
  }

  .empty-subtitle {
    margin: 0;
    font-size: 12px;
    color: #9ca3af;
  }

  .vs-dark & {
    color: #6b7280;

    .empty-title {
      color: #9ca3af;
    }
  }
}

/* Group container */
.group-container {
  margin-bottom: 4px;
}

/* Group header */
.group-header {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 16px;
  background: #f9fafb;
  font-size: 12px;
  font-weight: 700;
  color: #4b5563;
  border-bottom: 1px solid #e5e7eb;
  letter-spacing: -0.01em;

  .vs-dark & {
    background: #262626;
    color: #9ca3af;
    border-bottom-color: #333;
  }
}

.group-name {
  flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.group-score {
  font-size: 11px;
  font-weight: 600;
  color: #6b7280;

  .vs-dark & {
    color: #9ca3af;
  }
}

/* Case item */
.case-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  padding: 10px 16px;
  border: none;
  background: transparent;
  cursor: pointer;
  transition: all 0.12s;
  text-align: left;
  color: inherit;
  border-left: 3px solid transparent;

  &:hover {
    background: #f9fafb;

    .vs-dark & {
      background: rgba(255, 255, 255, 0.04);
    }
  }

  &:focus-visible {
    outline: 2px solid #3b82f6;
    outline-offset: -2px;
  }

  &.case-item--active {
    background: #eff6ff;
    border-left-color: #3b82f6;

    &:hover {
      background: #dbeafe;
    }

    .vs-dark & {
      background: rgba(59, 130, 246, 0.12);

      &:hover {
        background: rgba(59, 130, 246, 0.16);
      }
    }
  }

  &.case-item--grouped {
    padding-left: 28px;

    &.case-item--active {
      padding-left: 25px;
    }
  }
}

.case-item-left {
  display: flex;
  align-items: center;
  gap: 10px;
  overflow: hidden;
  flex: 1;
}

.case-name {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-size: 13px;
  font-weight: 500;
  color: #1f2937;

  .vs-dark & {
    color: #e5e5e5;
  }
}

.case-weight {
  font-size: 11px;
  font-weight: 600;
  color: #6b7280;
  padding: 2px 6px;
  background: #f3f4f6;
  border-radius: 4px;

  .vs-dark & {
    color: #9ca3af;
    background: #374151;
  }
}

.case-item-right {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-shrink: 0;
}

.case-score {
  font-size: 12px;
  font-weight: 600;
  font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas,
    'Courier New', monospace;
  color: #6b7280;

  .vs-dark & {
    color: #9ca3af;
  }
}

/* Verdict icons */
.verdict-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 20px;
  height: 20px;
  font-size: 11px;
  border-radius: 50%;
  flex-shrink: 0;
  font-weight: 800;

  &.verdict-ac {
    color: #059669;
    background: rgba(16, 185, 129, 0.15);

    .vs-dark & {
      color: #34d399;
      background: rgba(52, 211, 153, 0.18);
    }
  }

  &.verdict-pa {
    color: #d97706;
    background: rgba(245, 158, 11, 0.15);

    .vs-dark & {
      color: #fbbf24;
      background: rgba(251, 191, 36, 0.18);
    }
  }

  &.verdict-wa {
    color: #dc2626;
    background: rgba(220, 38, 38, 0.12);

    .vs-dark & {
      color: #f87171;
      background: rgba(248, 113, 113, 0.15);
    }
  }

  &.verdict-tle {
    color: #9333ea;
    background: rgba(147, 51, 234, 0.12);

    .vs-dark & {
      color: #c084fc;
      background: rgba(192, 132, 252, 0.15);
    }
  }

  &.verdict-pending {
    color: #9ca3af;
    background: #f3f4f6;

    .vs-dark & {
      color: #6b7280;
      background: rgba(156, 163, 175, 0.15);
    }
  }
}

/* Remove button */
.btn-remove {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  padding: 0;
  border: none;
  background: transparent;
  color: #9ca3af;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.12s;
  opacity: 0;

  .case-item:hover &,
  .case-item:focus-within & {
    opacity: 1;
  }

  &:hover {
    color: #dc2626;
    background: rgba(220, 38, 38, 0.1);
  }

  &:focus-visible {
    opacity: 1;
    outline: 2px solid #dc2626;
    outline-offset: 2px;
  }

  .vs-dark & {
    color: #6b7280;

    &:hover {
      color: #f87171;
      background: rgba(248, 113, 113, 0.15);
    }
  }
}

/* Loading overlay */
.loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.9);
  z-index: 100;

  .vs-dark & {
    background: rgba(30, 30, 30, 0.9);
  }
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #e5e7eb;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;

  .vs-dark & {
    border-color: #404040;
    border-top-color: #60a5fa;
  }
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  animation: fadeIn 0.15s ease;
}

.modal-content {
  background: #fff;
  border-radius: 12px;
  padding: 24px;
  max-width: 400px;
  width: 90%;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1),
    0 10px 10px -5px rgba(0, 0, 0, 0.04);
  animation: scaleIn 0.2s ease;

  .vs-dark & {
    background: #2a2a2a;
    color: #e5e5e5;
  }

  h3 {
    margin: 0 0 12px 0;
    font-size: 18px;
    font-weight: 600;
    color: #1a1a1a;

    .vs-dark & {
      color: #e5e5e5;
    }
  }

  p {
    margin: 0 0 20px 0;
    font-size: 14px;
    color: #4b5563;
    line-height: 1.5;

    .vs-dark & {
      color: #9ca3af;
    }

    strong {
      font-weight: 600;
      color: #1a1a1a;

      .vs-dark & {
        color: #e5e5e5;
      }
    }
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }

  to {
    opacity: 1;
  }
}

@keyframes scaleIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }

  to {
    opacity: 1;
    transform: scale(1);
  }
}

.modal-actions {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
}

.btn {
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;

  &:focus-visible {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
  }
}

.btn-secondary {
  background: #f3f4f6;
  color: #1a1a1a;

  &:hover {
    background: #e5e7eb;
  }

  .vs-dark & {
    background: #404040;
    color: #e5e5e5;

    &:hover {
      background: #525252;
    }
  }
}

.btn-danger {
  background: #dc2626;
  color: #fff;

  &:hover {
    background: #b91c1c;
  }
}

/* Scrollbar styling */
.cases-list::-webkit-scrollbar {
  width: 10px;
}

.cases-list::-webkit-scrollbar-track {
  background: transparent;
}

.cases-list::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 5px;
  border: 2px solid transparent;
  background-clip: padding-box;

  &:hover {
    background: #9ca3af;
    background-clip: padding-box;
  }

  .vs-dark & {
    background: #404040;
    background-clip: padding-box;

    &:hover {
      background: #525252;
      background-clip: padding-box;
    }
  }
}
</style>
