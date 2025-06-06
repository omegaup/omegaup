<template>
  <div class="root d-flex flex-column h-100" :class="theme">
    <div class="summary">
      {{ summary }}
    </div>
    <div class="filenames">
      <div class="list-group">
        <button
          v-if="!groups"
          class="list-group-item list-group-item-action disabled"
          type="button"
        >
          <em>{{ T.wordsEmpty }}</em>
        </button>
        <template v-for="group in groups" v-else :title="name">
          <div
            v-if="group.explicit"
            :key="group.name"
            class="list-group-item list-group-item-secondary"
          >
            <div>
              <span
                class="verdict"
                :class="verdictClass(groupResult(group.name))"
                :title="verdictTooltip(groupResult(group.name))"
                >{{ verdictLabel(groupResult(group.name))
                }}<span class="score">{{
                  score(groupResult(group.name))
                }}</span></span
              >
              <span :title="group.name">{{ group.name }}</span>
            </div>
          </div>
          <button
            v-for="item in group.cases"
            :key="item.name"
            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
            type="button"
            :class="{
              'in-group': group.explicit,
              active: currentCase == item.name,
            }"
            @click="selectCase(item.name)"
          >
            <div class="text-truncate">
              <span
                class="verdict"
                :title="verdictTooltip(caseResult(item.name))"
                >{{ verdictLabel(caseResult(item.name))
                }}<span class="score">{{
                  score(caseResult(item.name))
                }}</span></span
              >
              <span :title="item.name">{{ item.name }}</span>
            </div>
            <button
              v-if="groups.length > 1"
              :aria-label="T.wordsClose"
              class="close"
              type="button"
              @click.prevent.stop="removeCase(item.name)"
            >
              <span aria-hidden="true">×</span>
            </button>
          </button>
        </template>
      </div>
    </div>
    <form @submit.prevent="createCase()">
      <div class="input-group">
        <input
          v-model="newCaseWeight"
          class="form-control case-weight"
          type="text"
        />
        <input
          v-model="newCaseName"
          class="form-control"
          type="text"
          data-case-name
        />
        <div class="input-group-append">
          <button
            class="btn"
            :class="{
              'btn-primary': theme == 'vs',
              'btn-secondary': theme == 'vs-dark',
            }"
            type="submit"
            :disabled="!newCaseName.length"
            data-add-button
            @click="createCase()"
          >
            +
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import store from './GraderStore';
import { types } from '../api_types';
import { GraderResults, CaseSelectorGroup } from './GraderStore';
import T from '../lang';

@Component
export default class CaseSelector extends Vue {
  newCaseWeight: number = 1;
  newCaseName: string = '';
  T = T;

  get theme(): string {
    return store.getters['theme'];
  }
  get summary(): string {
    if (!store.state.results || !store.state.results.verdict) {
      return '…';
    }
    return `${store.state.results.verdict} ${this.score(store.state.results)}`;
  }

  get groups(): CaseSelectorGroup[] {
    return store.getters['caseSelectorGroups'];
  }

  get currentCase(): string {
    return store.getters['currentCase'];
  }

  set currentCase(value: string) {
    store.dispatch('currentCase', value);
  }

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
    if (!result) return '…';

    if (typeof result.verdict === 'undefined') {
      if (result.contest_score == result.max_score) return '✓';
      return '✗';
    }
    switch (result.verdict) {
      case 'CE':
        return '…';
      case 'AC':
        return '✓';
      case 'PA':
        return '½';
      case 'WA':
        return '✗';
      case 'TLE':
        return '⌚';
    }
    return ' ☹';
  }

  verdictClass(result: null | types.RunDetailsGroup): string {
    if (!result) return '';
    return result.verdict || '';
  }

  verdictTooltip(
    result: null | types.RunDetailsGroup | types.CaseResult,
  ): string {
    if (!result) return '';
    if (typeof result.verdict !== 'undefined') {
      return `${result.verdict} ${this.score(result)}`;
    }
    return this.score(result);
  }

  score(
    result: null | types.RunDetailsGroup | types.CaseResult | GraderResults,
  ): string {
    if (!result) return '…';
    return `${this.formatNumber(result.contest_score)}/${this.formatNumber(
      result.max_score || 0,
    )}`;
  }

  formatNumber(value: number): string {
    const str = value.toFixed(2);
    if (str.endsWith('.00')) return str.substring(0, str.length - 3);
    return str;
  }

  selectCase(name: string): void {
    this.currentCase = name;
  }

  createCase(): void {
    if (!this.newCaseName) return;

    store.dispatch('createCase', {
      name: this.newCaseName,
      weight: parseFloat(this.newCaseWeight.toString()),
    });

    this.newCaseWeight = 1;
    this.newCaseName = '';
  }

  removeCase(name: string): void {
    store.dispatch('removeCase', name);
  }
}
</script>

<style lang="scss" scoped>
@import '../../../sass/main.scss';

button.in-group {
  border-left-width: 6px;
  padding-left: 15px;
}

button[type='submit'] {
  width: 2em;
}

div.summary {
  text-align: center;
  padding: 0.25em;
}

span.verdict {
  display: inline-block;
  float: left;
  width: 2em;
  text-align: center;
  margin: -7px 5px -6px -10px;
}

span.verdict span.score {
  font-size: xx-small;
  display: block;
}

div.filenames {
  overflow-y: auto;
  flex: 1;
}

input[type='number'].case-weight {
  width: 3em;
}

.list-group-item-secondary {
  font-weight: bold;
  border-left-width: 6px;
  padding-left: 15px;
}

/* Dark theme styles */
.vs-dark .summary {
  color: var(--vs-dark-font-color);
  background-color: var(--vs-dark-background-color);
}

.vs-dark .list-group-item {
  background-color: var(--vs-dark-background-color);
  color: var(--vs-dark-font-color);
  border-color: var(--vs-dark-border-color-medium);
}

.vs-dark .list-group-item-action {
  background-color: var(--vs-dark-background-color);
  color: var(--vs-dark-font-color);
}

.vs-dark .list-group-item-action:hover,
.vs-dark .list-group-item-action:focus {
  background-color: var(
    --vs-dark-list-group-item-action-background-color--hover
  );
  color: var(--vs-dark-font-color);
}

.vs-dark .list-group-item-action.active {
  background-color: var(
    --vs-dark-list-group-item-action-background-color--active
  );
  border-color: var(--vs-dark-border-color-strong);
}

.vs-dark .list-group-item-secondary {
  background-color: var(--vs-dark-list-group-item-secondary-background-color);
  color: var(--vs-dark-font-color);
}

.vs-dark .close {
  color: var(--vs-dark-close-color);
}

.vs-dark .close:hover {
  color: var(--vs-dark-close-color--hover);
}

.vs-dark .input-group input.form-control {
  background-color: var(--vs-dark-background-color);
  color: var(--vs-dark-font-color);
  border-color: var(--vs-dark-border-color-medium);
}

.vs-dark .btn-secondary {
  background-color: var(--vs-dark-btn-secondary-background-color);
  border-color: var(--vs-dark-border-color-strong);
}

.vs-dark .btn-secondary:hover {
  background-color: var(--vs-dark-btn-secondary-background-color--hover);
}

.vs-dark .verdict {
  color: var(--vs-dark-font-color);
}
</style>
