<template>
  <div data-arena-wrapper :class="backgroundClass">
    <div class="text-center mt-4 pt-2">
      <h2 v-if="title !== null && !isScrolled" class="mb-4">
        <span>{{ title }}</span>
        <slot name="socket-status">
          <sup class="socket-status-error" title="WebSocket">✗</sup>
        </slot>
        <slot name="edit-button"></slot>
      </h2>
      <slot v-if="!isScrolled" name="clock"><div class="clock">∞</div></slot>
    </div>
    <div class="nav-tabs-wrapper">
      <div v-if="isScrolled && title !== null" class="compact-header">
        <h2 class="compact-title">
          <span>{{ title }}</span>
          <slot name="socket-status">
            <sup class="socket-status-error" title="WebSocket">✗</sup>
          </slot>
          <slot name="edit-button"></slot>
        </h2>
        <div class="compact-clock">
          <slot name="clock"><div class="clock">∞</div></slot>
        </div>
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
            <span
              v-if="tab.name === 'clarifications' && clarifications.length"
              :class="{ unread: unreadClarifications }"
              >({{ clarifications.length }})</span
            >
          </a>
        </li>
      </ul>
    </div>
    <div class="tab-content" @scroll="onScroll">
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
import { types } from '../../api_types';
import T from '../../lang';
import { Tab } from '../problem/Details.vue';

@Component
export default class Arena extends Vue {
  @Prop({ default: false }) shouldShowRuns!: boolean;
  @Prop({ default: true }) shouldShowRanking!: boolean;
  @Prop({ default: () => [] }) clarifications!: types.Clarification[];
  @Prop() title!: string;
  @Prop() activeTab!: string;
  @Prop() backgroundClass!: string;

  T = T;
  selectedTab = this.activeTab;
  clarificationsHaveBeenRead = false;
  isScrolled = false;
  scrollThreshold = 50;

  get unreadClarifications() {
    return (
      this.activeTab !== 'clarifications' && !this.clarificationsHaveBeenRead
    );
  }

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
        visible: this.shouldShowRanking,
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

  onScroll(event: Event): void {
    const target = event.target as HTMLElement;
    this.isScrolled = target.scrollTop > this.scrollThreshold;
  }

  @Emit('update:activeTab')
  onTabSelected(tabName: string): string {
    if (tabName === 'clarifications') {
      this.clarificationsHaveBeenRead = true;
    }
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
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  max-height: 100vh;
  overflow: hidden;
}

[data-arena-wrapper] > .nav-tabs-wrapper {
  flex-shrink: 0;
  position: relative;
}

[data-arena-wrapper] > .nav-tabs-wrapper > .compact-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.25rem 1rem;
  margin-bottom: 0.5rem;
  transition: opacity 0.2s ease;
  flex-wrap: wrap;
  gap: 0.5rem;
}

[data-arena-wrapper] > .nav-tabs-wrapper > .compact-header > .compact-title {
  margin: 0;
  font-size: 1.25rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex: 1;
  min-width: 0;
}

[data-arena-wrapper] > .nav-tabs-wrapper > .compact-header > .compact-clock {
  display: flex;
  align-items: center;
  flex-shrink: 0;
}

[data-arena-wrapper]
  > .nav-tabs-wrapper
  > .compact-header
  > .compact-clock
  .clock {
  font-size: 1.5rem;
  line-height: 1;
}

[data-arena-wrapper] > .tab-content {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  min-height: 0;
  position: relative;
  scrollbar-width: none;
}

.socket-status-error {
  color: var(--arena-socket-status-error-color);
}

.socket-status-ok {
  color: var(--arena-socket-status-ok-color);
}

.socket-status {
  cursor: help;
}

.clock {
  font-size: 3em;
  line-height: 0.4em;
}

.navleft {
  overflow: hidden;
}

.navleft .navbar {
  background: transparent;
}

.navleft .main {
  border: 1px solid var(--arena-navbar-left-border-color);
  border-width: 0 0 1px 1px;
}

.problem {
  background: var(--arena-problem-background-color);
  padding: 1em;
}

.unread {
  font-weight: bold;
}

@media only screen and (min-width: 960px) {
  .navleft {
    .navbar {
      width: 21em;
      float: left;
    }

    .main {
      margin-left: 20em;
    }
  }
  .problem {
    margin-top: -1.5em;
    margin-right: -1em;
  }
}
</style>
