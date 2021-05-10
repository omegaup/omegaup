<template>
  <div data-arena-wrapper :class="backgroundClass">
    <div class="text-center mt-4 pt-2">
      <h2>
        <span>{{ contestTitle }}</span>
        <slot name="socket-status">
          <sup class="socket-status-error" title="WebSocket">✗</sup>
        </slot>
      </h2>
      <slot name="clock"><div class="clock">∞</div></slot>
    </div>
    <ul class="nav justify-content-center nav-tabs mt-4">
      <li
        v-for="tab in availableTabs"
        :key="tab.name"
        class="nav-item"
        role="tablist"
      >
        <a
          :href="`#${tab.name}`"
          class="nav-link"
          data-toggle="tab"
          role="tab"
          :aria-controls="tab.name"
          :class="{ active: selectedTab === tab.name }"
          :aria-selected="selectedTab === tab.name"
          @click="onTabSelected(tab.name)"
        >
          {{ tab.text }}
        </a>
      </li>
    </ul>
    <div class="tab-content">
      <div
        class="tab-pane fade"
        :class="{ 'show active': selectedTab === 'problems' }"
      >
        <slot name="arena-problems"></slot>
      </div>
      <div
        class="tab-pane fade"
        :class="{ 'show active': selectedTab === 'ranking' }"
      >
        <slot name="arena-scoreboard"></slot>
      </div>
      <div
        class="tab-pane fade"
        :class="{ 'show active': selectedTab === 'runs' }"
      >
        <!-- TODO: Add Runs component when we migrate arena.contest.admin.tpl-->
        <slot name="arena-runs"></slot>
      </div>
      <div
        class="tab-pane fade"
        :class="{ 'show active': selectedTab === 'clarifications' }"
      >
        <slot name="arena-clarifications"></slot>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Emit } from 'vue-property-decorator';
import T from '../../lang';
import { Tab } from '../problem/Details.vue';

@Component
export default class Arena extends Vue {
  @Prop({ default: false }) shouldShowRuns!: boolean;
  @Prop() contestTitle!: string;
  @Prop() activeTab!: string;
  @Prop() backgroundClass!: string;

  T = T;
  selectedTab = this.activeTab;

  get availableTabs(): Tab[] {
    const tabs = [
      {
        name: 'problems',
        text: T.wordsProblems,
        visible: true,
      },
      {
        name: 'ranking',
        text: T.wordsRanking,
        visible: true,
      },
      {
        name: 'runs',
        text: T.wordsRuns,
        visible: this.shouldShowRuns,
      },
      {
        name: 'clarifications',
        text: T.wordsClarifications,
        visible: true,
      },
    ];
    return tabs.filter((tab) => tab.visible);
  }

  @Emit('update:activeTab')
  onTabSelected(tabName: string): string {
    this.selectedTab = tabName;
    return this.selectedTab;
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
.practice {
  background: var(--arena-practice-background-color) url(/media/gradient.png)
    repeat-x 0 0 !important;

  .nav-tabs .nav-link {
    background-color: var(--arena-contest-navtabs-link-background-color);
    border-top-color: var(--arena-contest-navtabs-link-border-top-color);
  }
}

[data-arena-wrapper] {
  background: var(--arena-background-color);
  font-family: sans-serif;
  overflow-y: auto;
}

.socket-status-error {
  color: var(--arena-socket-status-error-color);
}

.socket-status-ok {
  color: var(--arena-socket-status-ok-color);
}

.clock {
  font-size: 3em;
  line-height: 0.4em;
}

.navleft {
  overflow: hidden;
}

.navleft .navbar {
  width: 21em;
  float: left;
  background: transparent;
}

.navleft .main {
  margin-left: 20em;
  border: 1px solid var(--arena-navbar-left-border-color);
  border-width: 0 0 1px 1px;
}

.problem {
  background: var(--arena-problem-background-color);
  padding: 1em;
  margin-top: -1.5em;
  margin-right: -1em;
}
</style>
