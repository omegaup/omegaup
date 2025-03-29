Here's the formatted code with changes highlighted for GitHub:

```vue
<template>
  <div>
    <div class="col-sm-12">
      <h1 class="title">{{ T.wordsContests }}</h1>
    </div>
    <b-card no-body>
      <b-tabs
        class="sidebar"
        pills
        card
        vertical
        nav-wrapper-class="contest-list-nav col-md-2 col-sm-12 test-class"
      >
        <b-card class="card-group-menu">
          <b-container>
            <b-row class="justify-content-between" align-v="center">
              <b-col class="col-12 col-md-5 mb-2 mb-md-0 p-0">
                <form @submit.prevent="onSearchQuery">
                  <div class="input-group">
                    <input
                      v-model.lazy="currentQuery"
                      class="form-control nav-link"
                      type="text"
                      name="query"
                      autocomplete="off"
                      autocorrect="off"
                      autocapitalize="off"
                      spellcheck="false"
                      :placeholder="T.wordsKeyword"
                      @keyup.enter="onSearchQuery"
                    />
                    <button
                      class="btn reset-btn nav-link"
                      type="reset"
                      @click="onReset"
                    >
                      &times;
                    </button>
                    <div class="input-group-append">
                      <input
                        class="btn btn-primary btn-style btn-md btn-block active nav-link"
                        type="submit"
                        :value="T.wordsSearch"
                      />
                    </div>
                  </div>
                </form>
              </b-col>
              <b-col sm="12" class="d-flex col-md-6 btns-group p-0">
                <b-dropdown ref="dropdownOrderBy" no-caret>
                  <template #button-content>
                    <div>
                      <font-awesome-icon icon="sort-amount-down" />
                      {{ T.contestOrderBy }}
                    </div>
                  </template>
                  <b-dropdown-item
                    href="#"
                    data-order-by-ends
                    @click="orderByEnds"
                  >
                    <font-awesome-icon
                      v-if="currentOrder === ContestOrder.Ends"
                      icon="check"
                      class="mr-1"
                    />{{ T.contestOrderByEnds }}</b-dropdown-item
                  >
                  <b-dropdown-item
                    href="#"
                    data-order-by-title
                    @click="orderByTitle"
                  >
                    <font-awesome-icon
                      v-if="currentOrder === ContestOrder.Title"
                      icon="check"
                      class="mr-1"
                    />{{ T.contestOrderByTitle }}</b-dropdown-item
                  >
                  <b-dropdown-item
                    href="#"
                    data-order-by-duration
                    @click="orderByDuration"
                  >
                    <font-awesome-icon
                      v-if="currentOrder === ContestOrder.Duration"
                      icon="check"
                      class="mr-1"
                    />{{ T.contestOrderByDuration }}</b-dropdown-item
                  >
                  <b-dropdown-item
                    href="#"
                    data-order-by-organizer
                    @click="orderByOrganizer"
                  >
                    <font-awesome-icon
                      v-if="currentOrder === ContestOrder.Organizer"
                      icon="check"
                      class="mr-1"
                    />{{ T.contestOrderByOrganizer }}</b-dropdown-item
                  >
                  <b-dropdown-item
                    href="#"
                    data-order-by-contestants
                    @click="orderByContestants"
                  >
                    <font-awesome-icon
                      v-if="currentOrder === ContestOrder.Contestants"
                      icon="check"
                      class="mr-1"
                    />{{ T.contestOrderByContestants }}</b-dropdown-item
                  >
                  <b-dropdown-item
                    href="#"
                    data-order-by-signed-up
                    @click="orderBySignedUp"
                  >
                    <font-awesome-icon
                      v-if="currentOrder === ContestOrder.SignedUp"
                      icon="check"
                      class="mr-1"
                    />{{ T.contestOrderBySignedUp }}</b-dropdown-item
                  >
                </b-dropdown>
                <b-dropdown ref="dropdownFilterBy" class="mr-0" no-caret>
                  <template #button-content>
                    <font-awesome-icon icon="filter" />
                    {{ T.contestFilterBy }}
                  </template>
                  <b-dropdown-item
                    href="#"
                    data-filter-by-all
                    @click="filterByAll"
                  >
                    <font-awesome-icon
                      v-if="currentFilter === ContestFilter.All"
                      icon="check"
                      class="mr-1"
                    />{{ T.contestFilterByAll }}</b-dropdown-item
                  >
                  <b-dropdown-item
                    href="#"
                    data-filter-by-signed-up
                    @click="filterBySignedUp"
                  >
                    <font-awesome-icon
                      v-if="currentFilter === ContestFilter.SignedUp"
                      icon="check"
                      class="mr-1"
                    />{{ T.contestFilterBySignedUp }}</b-dropdown-item
                  >
                  <b-dropdown-item
                    href="#"
                    data-filter-by-recommended
                    @click="filterByRecommended"
                  >
                    <font-awesome-icon
                      v-if="currentFilter === ContestFilter.OnlyRecommended"
                      icon="check"
                      class="mr-1"
                    />{{ T.contestFilterByRecommended }}</b-dropdown-item
                  >
                </b-dropdown>
              </b-col>
            </b-row>
          </b-container>
        </b-card>
        <b-tab
          ref="currentContestTab"
          class="scroll-content"
          :title="T.contestListCurrent"
          :title-link-class="titleLinkClass(ContestTab.Current)"
          :active="currentTab === ContestTab.Current"
          @click="currentTab = ContestTab.Current"
        >
          <template v-if="loading || refreshing">
            <div v-for="index in 3" :key="index" class="card contest-card mb-3">
              <div class="line"></div>
            </div>
          </template>
          <div v-else-if="contestListEmpty" class="empty-category">
            {{ T.contestListEmpty }}
          </div>
          <template v-else>
            <omegaup-contest-card
              v-for="contestItem in contestList"
              :key="contestItem.contest_id"
              :contest="contestItem"
            >
              <template #contest-button-scoreboard>
                <div></div>
              </template>
              <template #text-contest-date>
                <b-card-text>
                  <font-awesome-icon icon="calendar-alt" />
                  <a :href="getTimeLink(contestItem.finish_time)">
                    {{
                      ui.formatString(T.contestEndTime, {
                        endDate: finishContestDate(contestItem),
                      })
                    }}
                  </a>
                </b-card-text>
              </template>
              <template #contest-dropdown>
                <div></div>
              </template>
            </omegaup-contest-card>
          </template>
          <template v-if="isScrollLoading">
            <div
              v-for="index in 3"
              :key="'scroll-' + index"
              class="card contest-card mb-3"
            >
              <div class="line"></div>
            </div>
          </template>
        </b-tab>
        <b-tab
          ref="futureContestTab"
          class="scroll-content"
          :title="T.contestListFuture"
          :title-link-class="titleLinkClass(ContestTab.Future)"
          :active="currentTab === ContestTab.Future"
          @click="currentTab = ContestTab.Future"
        >
          <template v-if="loading || refreshing">
            <div v-for="index in 3" :key="index" class="card contest-card mb-3">
              <div class="line"></div>
            </div>
          </template>
          <div v-else-if="contestListEmpty" class="empty-category">
            {{ T.contestListEmpty }}
          </div>
          <template v-else>
            <omegaup-contest-card
              v-for="contestItem in contestList"
              :key="contestItem.contest_id"
              :contest="contestItem"
            >
              <template #contest-button-scoreboard>
                <div></div>
              </template>
              <template #text-contest-date>
                <b-card-text>
                  <font-awesome-icon icon="calendar-alt" />
                  <a :href="getTimeLink(contestItem.start_time)">
                    {{
                      ui.formatString(T.contestStartTime, {
                        startDate: startContestDate(contestItem),
                      })
                    }}
                  </a>
                </b-card-text>
              </template>
              <template #contest-button-enter>
                <div></div>
              </template>
              <template #contest-dropdown>
                <div></div>
              </template>
            </omegaup-contest-card>
          </template>
          <template v-if="isScrollLoading">
            <div
              v-for="index in 3"
              :key="'scroll-' + index"
              class="card contest-card mb-3"
            >
              <div class="line"></div>
            </div>
          </template>
        </b-tab>
        <b-tab
          ref="pastContestTab"
          class="scroll-content"
          :title="T.contestListPast"
          :title-link-class="titleLinkClass(ContestTab.Past)"
          :active="currentTab === ContestTab.Past"
          @click="currentTab = ContestTab.Past"
        >
          <template v-if="loading || refreshing">
            <div v-for="index in 3" :key="index" class="card contest-card mb-3">
              <div class="line"></div>
            </div>
          </template>
          <div v-else-if="contestListEmpty" class="empty-category">
            {{ T.contestListEmpty }}
          </div>
          <template v-else>
            <omegaup-contest-card
              v-for="contestItem in contestList"
              :key="contestItem.contest_id"
              :contest="contestItem"
            >
              <template #contest-enroll-status>
                <div></div>
              </template>
              <template #text-contest-date>
                <b-card-text>
                  <font-awesome-icon icon="calendar-alt" />
                  <a :href="getTimeLink(contestItem.start_time)">
                    {{
                      ui.formatString(T.contestStartedTime, {
                        startedDate: startContestDate(contestItem),
                      })
                    }}
                  </a>
                </b-card-text>
              </template>
              <template #contest-button-enter>
                <div></div>
              </template>
              <template #contest-button-see-details>
                <div></div>
              </template>
            </omegaup-contest-card>
          </template>
          <template v-if="isScrollLoading">
            <div
              v-for="index in 3"
              :key="'scroll-' + index"
              class="card contest-card mb-3"
            >
              <div class="line"></div>
            </div>
          </template>
        </b-tab>
      </b-tabs>
    </b-card>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { types } from '../../api_types';
import * as ui from '../../ui';
import T from '../../lang';

// Import Bootstrap an BootstrapVue CSS files (order is important)
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';

// Import Only Required Plugins
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
import {
  TabsPlugin,
  CardPlugin,
  DropdownPlugin,
  LayoutPlugin,
} from 'bootstrap-vue';
import ContestCard from './ContestCard.vue';
import infiniteScroll from 'vue-infinite-scroll';
Vue.use(TabsPlugin);
Vue.use(CardPlugin);
Vue.use(DropdownPlugin);
Vue.use(LayoutPlugin);
library.add(fas);

export enum ContestTab {
  Current = 'current',
  Future = 'future',
  Past = 'past',
}

export enum ContestOrder {
  None = 'none',
  Title = 'title',
  Ends = 'ends',
  Duration = 'duration',
  Organizer = 'organizer',
  Contestants = 'contestants',
  SignedUp = 'signedup',
}

export enum ContestFilter {
  SignedUp = 'signedup',
  OnlyRecommended = 'recommended',
  All = 'all',
}

export interface UrlParams {
  page: number;
  tab_name: ContestTab;
  query: string;
  sort_order: ContestOrder;
  filter: ContestFilter;
}

@Component({
  components: {
    'omegaup-contest-card': ContestCard,
    FontAwesomeIcon,
  },
  directives: {
    infiniteScroll,
  },
})
export default class ArenaContestList extends Vue {
  @Prop({ default: null }) countContests!: { [key: string]: number } | null;
  @Prop() contests!: types.ContestList;
  @Prop() query!: string;
  @Prop() tab!: ContestTab;
  @Prop({ default: ContestOrder.None }) sortOrder!: ContestOrder;
  @Prop({ default: ContestFilter.All }) filter!: ContestFilter;
  @Prop() page!: number;
  @Prop({ default: 10 }) pageSize!: number;
  @Prop({ default: false }) loading!: boolean;

  T = T;
  ui = ui;
  ContestTab = ContestTab;
  ContestOrder = ContestOrder;
  ContestFilter = ContestFilter;
  currentTab: ContestTab = this.tab;
  currentQuery: string = this.query;
  currentOrder: ContestOrder = this.sortOrder;
  currentFilter: ContestFilter = this.filter;
  currentPage: number = this.page;
  refreshing: boolean = true; // Start with refreshing true to show skeleton on initial load
  isScrollLoading: boolean = false;
  hasMore: boolean = true;

  titleLinkClass(tab: ContestTab) {
    if (this.currentTab === tab) {
      return ['text-center', 'active-title-link'];
    } else {
      return ['text-center', 'title-link'];
    }
  }

  onSearchQuery() {
    const urlObj = new URL(window.location.href);
    const params: UrlParams = {
      page: 1,
      tab_name:
        (urlObj.searchParams.get('tab_name') as ContestTab) ||
        ContestTab.Current,
      query: this.currentQuery,
      sort_order:
        (urlObj.searchParams.get('sort_order') as ContestOrder) ||
        ContestOrder.None,
      filter:
        (urlObj.searchParams.get('filter') as ContestFilter) ||
        ContestFilter.All,
    };
    this.currentPage = 1;
    this.hasMore = true;
    this.refreshing = true; // Set refreshing to true when searching
    this.fetchPage(params, urlObj);
  }
  
  onReset() {
    this.currentQuery = '';
  }
  
  fetchInitialContests() {
    this.refreshing = true; // Set refreshing to true when fetching initial contests
    const urlObj = new URL(window.location.href);
    const params: UrlParams = {
      page: 1,
      tab_name: this.currentTab,
      query: this.currentQuery,
      sort_order: this.currentOrder,
      filter: this.currentFilter,
    };
    // Reset the contest list for this tab to avoid stale data
    if (this.contests) {
      Vue.set(this.contests, this.currentTab, []);
    } else {
      // Initialize contests object if it doesn't exist
      this.contests = {
        current: [],
        future: [],
        past: []
      } as types.ContestList;
    }
    this.currentPage = 1;
    this.hasMore = true;
    this.fetchPage(params, urlObj);
  }
  
  mounted() {
    window.addEventListener('scroll', this.handleScroll);
    // Initialize contests if they don't exist
    if (!this.contests) {
      this.contests = {
        current: [],
        future: [],
        past: []
      } as types.ContestList;
    }
    this.fetchInitialContests();
  }

  beforeDestroy() {
    window.removeEventListener('scroll', this.handleScroll);
  }

  handleScroll() {
    const bottomOfWindow =
      window.innerHeight + window.scrollY >=
      document.documentElement.scrollHeight - 250;

    if (
      !this.contestListEmpty &&
      bottomOfWindow &&
      !this.isScrollLoading &&
      this.hasMore
    ) {
      this.loadMoreContests();
    }
  }
  
  async loadMoreContests() {
    if (this.isScrollLoading || !this.hasMore) return;

    this.isScrollLoading = true;
    const nextPage = this.currentPage + 1;
    const urlObj = new URL(window.location.href);
    const params: UrlParams = {
      page: nextPage,
      tab_name: this.currentTab,
      query: this.currentQuery,
      sort_order: this.currentOrder,
      filter: this.currentFilter,
    };

    try {
      await this.fetchPage(params, urlObj);
      this.currentPage = nextPage;
      // Check if there are more contests to load (based on pageSize)
      this.hasMore = this.contestList.length % this.pageSize === 0;
    } finally {
      this.isScrollLoading = false;
    }
  }

  fetchPage(params: UrlParams, urlObj: URL) {
    this.$emit('fetch-page', { params, urlObj });
    // Set a timeout to turn off refreshing if it takes too long
    setTimeout(() => {
      this.refreshing = false;
    }, 3000);
  }

  finishContestDate(contest: types.ContestListItem): string {
    return contest.finish_time.toLocaleDateString();
  }

  startContestDate(contest: types.ContestListItem): string {
    return contest.start_time.toLocaleDateString();
  }

  getTimeLink(time: Date): string {
    return `http://timeanddate.com/worldclock/fixedtime.html?iso=${time.toISOString()}`;
  }

  orderByTitle() {
    this.currentOrder = ContestOrder.Title;
    this.refreshing = true;
    this.fetchInitialContests();
  }

  orderByEnds() {
    this.currentOrder = ContestOrder.Ends;
    this.refreshing = true;
    this.fetchInitialContests();
  }

  orderByDuration() {
    this.currentOrder = ContestOrder.Duration;
    this.refreshing = true;
    this.fetchInitialContests();
  }

  orderByOrganizer() {
    this.currentOrder = ContestOrder.Organizer;
    this.refreshing = true;
    this.fetchInitialContests();
  }

  orderByContestants() {
    this.currentOrder = ContestOrder.Contestants;
    this.refreshing = true;
    this.fetchInitialContests();
  }

  orderBySignedUp() {
    this.currentOrder = ContestOrder.SignedUp;
    this.refreshing = true;
    this.fetchInitialContests();
  }

  filterBySignedUp() {
    this.currentFilter = ContestFilter.SignedUp;
    this.refreshing = true;
    this.fetchInitialContests();
  }
  
  filterByRecommended() {
    this.currentFilter = ContestFilter.OnlyRecommended;
    this.refreshing = true;
    this.fetchInitialContests();
  }
  
  filterByAll() {
    this.currentFilter = ContestFilter.All;
    this.refreshing = true;
    this.fetchInitialContests();
  }

  get contestList(): types.ContestListItem[] {
    if (!this.contests) {
      return [];
    }
    
    switch (this.currentTab) {
      case ContestTab.Current:
        return this.contests.current || [];
      case ContestTab.Past:
        return this.contests.past || [];
      case ContestTab.Future:
        return this.contests.future || [];
      default:
        return this.contests.current || [];
    }
  }

  get contestListEmpty(): boolean {
    if (this.loading || this.refreshing) return false;
    if (!this.contestList) return true;
    return this.contestList.length === 0;
  }
  
  @Watch('currentTab', { immediate: true, deep: true })
  onCurrentTabChanged(newValue: ContestTab, oldValue: undefined | ContestTab) {
    if (typeof oldValue === 'undefined') return;
    this.refreshing = true; // Show skeleton when changing tab
    this.fetchInitialContests();
  }

  @Watch('currentOrder', { immediate: true, deep: true })
  onCurrentOrderChanged(
    newValue: ContestOrder,
    oldValue: undefined | ContestOrder,
  ) {
    if (typeof oldValue === 'undefined') return;
    // Order changes are now handled in the order methods
  }

  @Watch('currentFilter', { immediate: true, deep: true })
  onCurrentFilterChanged(
    newValue: ContestFilter,
    oldValue: undefined | ContestFilter,
  ) {
    if (typeof oldValue === 'undefined') return;
    // Filter changes are now handled in the filter methods
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.btn {
  padding: 0.5rem 1rem !important;
}

.card-group-menu {
  position: sticky;
  top: 62px;
  z-index: 10;
  border: none;
  border-bottom: 1px solid var(--arena-scoreboard-hover-color);
  border-radius: 0.25rem 0.25rem 0 0;
}

.title {
  text-align: center;
  font-size: 2rem;
  margin-bottom: 1.8rem;
}

.btn-style {
  border-color: var(--arena-button-border-color);
}

.form-control {
  height: auto;
}

.reset-btn {
  line-height: 1.5;
  color: #6c757d;
  background-color: var(--arena-runs-table-status-je-ve-font-color);
  background-clip: padding-box;
  border: none;
  border-top: 1px solid var(--arena-reset-border-color);
  border-bottom: 1px solid var(--arena-reset-border-color);
  border-radius: unset;
}

.btn-primary {
  background-color: var(--arena-button-border-color) !important;
  height: 2.5rem;
  width: 7.5rem;
  display: flex;
  justify-content: center;
  align-items: center;
}

.contest-card {
  height: 150px;
  padding: 1rem;
}

.line {
  height: 100%;
  background: var(--arena-submissions-list-skeletonloader-final-background-color);
  border-radius: 8px;
  animation: loading 1.5s infinite;
}

@keyframes loading {
  0% {
    background: var(--arena-submissions-list-skeletonloader-initial-background-color);
  }

  50% {
    background: var(--arena-submissions-list-skeletonloader-final-background-color);
  }

  100% {
    background: var(--arena-submissions-list-skeletonloader-initial-background-color);
  }
}

.sidebar {
  >>>.contest-list-nav {
    background-color: var(--arena-contest-list-sidebar-tab-list-background-color);

    .active-title-link {
      background-color: var(--arena-contest-list-sidebar-tab-list-link-background-color--active) !important;
    }

    .title-link {
      color: var(--arena-contest-list-sidebar-tab-list-link-font-color) !important;
    }
  }
}

.empty-category {
  text-align: center;
  font-size: 200%;
  margin: 1em;
  color: var(--arena-contest-list-empty-category-font-color);
}

.btns-group {
  justify-content: flex-end;

  .dropdown {
    margin-right: 1rem;
  }
}

@media screen and (max-width: 768px) {
  .title {
    font-size: 1.5rem;
    text-align: center;
  }

  .tabs {
    flex-direction: column;
  }

  .btns-group {
    justify-content: flex-start;

    .dropdown {
      flex: 1;
      gap: 1rem;
      margin-right: 0.8rem;
    }
  }
}
</style>
