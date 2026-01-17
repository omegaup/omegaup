<template>
  <div>
    <div class="col-sm-12">
      <h1 class="title">{{ T.wordsContests }}</h1>
    </div>

    <!-- Search and Filter Section -->
    <b-card class="mb-4">
      <b-container>
        <b-row class="justify-content-between" align-v="center">
          <b-col class="col-12 col-md-5 mb-2 mb-md-0 p-0">
            <form @submit.prevent="onSearchQuery">
              <div class="input-group">
                <input
                  v-model="currentQuery"
                  class="form-control nav-link"
                  type="text"
                  name="query"
                  autocomplete="off"
                  autocorrect="off"
                  autocapitalize="off"
                  spellcheck="false"
                  :placeholder="T.wordsKeyword"
                  @input="onSearchQueryDebounced"
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
            <b-dropdown ref="dropdownOrderBy" no-caret data-dropdown-order>
              <template #button-content>
                <div>
                  <font-awesome-icon icon="sort-amount-down" />
                  {{ T.contestOrderBy }}
                </div>
              </template>
              <b-dropdown-item href="#" data-order-by-ends @click="orderByEnds">
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
            <b-dropdown
              ref="dropdownFilterBy"
              class="mr-0"
              no-caret
              data-dropdown-filter
            >
              <template #button-content>
                <font-awesome-icon icon="filter" />
                {{ T.contestFilterBy }}
              </template>
              <b-dropdown-item href="#" data-filter-by-all @click="filterByAll">
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

    <!-- Summary View (Horizontal Scrolling) -->
    <div v-if="!viewAllCategory">
      <div
        v-for="(tab, index) in [
          ContestTab.Current,
          ContestTab.Future,
          ContestTab.Past,
        ]"
        :key="tab"
        class="mb-5 section-container"
        :class="{ 'section-separator': index < 2 }"
      >
        <div
          class="d-flex justify-content-between align-items-center mb-3 px-3"
        >
          <h3 class="m-0">{{ getTabTitle(tab) }}</h3>
          <b-button
            v-if="getContestsForTab(tab).length > 0"
            variant="link"
            @click="setViewAll(tab)"
          >
            {{ T.wordsViewAll }}
          </b-button>
        </div>

        <div class="position-relative scroll-wrapper">
          <b-button
            v-if="canScrollLeft(tab)"
            variant="light"
            class="scroll-btn scroll-left shadow-sm"
            @click="scrollLeft(tab)"
          >
            <font-awesome-icon icon="chevron-left" />
          </b-button>

          <div
            :ref="`scrollContainer_${tab}`"
            class="horizontal-scroll-container px-3 pb-3"
            @scroll="onScroll(tab)"
          >
            <div
              v-if="getContestsForTab(tab).length === 0"
              class="text-muted font-italic ml-3"
            >
              {{ T.contestListEmpty }}
            </div>
            <div v-else class="d-flex">
              <div
                v-for="contestItem in getContestsForTab(tab).slice(0, 10)"
                :key="contestItem.contest_id"
                class="mr-3"
                style="min-width: 300px; max-width: 300px"
              >
                <omegaup-contest-card :contest="contestItem">
                  <!-- Slots -->
                  <template #contest-button-scoreboard>
                    <div
                      v-if="
                        tab === ContestTab.Current || tab === ContestTab.Future
                      "
                    ></div>
                  </template>

                  <template #text-contest-date>
                    <b-card-text v-if="tab === ContestTab.Current">
                      <font-awesome-icon icon="calendar-alt" />
                      <a :href="getTimeLink(contestItem.finish_time)">
                        {{
                          ui.formatString(T.contestEndTime, {
                            endDate: finishContestDate(contestItem),
                          })
                        }}
                      </a>
                    </b-card-text>
                    <b-card-text v-else-if="tab === ContestTab.Future">
                      <font-awesome-icon icon="calendar-alt" />
                      <a :href="getTimeLink(contestItem.start_time)">
                        {{
                          ui.formatString(T.contestStartTime, {
                            startDate: startContestDate(contestItem),
                          })
                        }}
                      </a>
                    </b-card-text>
                    <b-card-text v-else-if="tab === ContestTab.Past">
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

                  <template #contest-dropdown>
                    <div
                      v-if="
                        tab === ContestTab.Current || tab === ContestTab.Future
                      "
                    ></div>
                  </template>

                  <template #contest-button-enter>
                    <div
                      v-if="
                        tab === ContestTab.Future || tab === ContestTab.Past
                      "
                    ></div>
                  </template>

                  <template #contest-enroll-status>
                    <div v-if="tab === ContestTab.Past"></div>
                  </template>

                  <template #contest-button-see-details>
                    <div v-if="tab === ContestTab.Past"></div>
                  </template>
                </omegaup-contest-card>
              </div>
            </div>
          </div>

          <b-button
            v-if="canScrollRight(tab)"
            variant="light"
            class="scroll-btn scroll-right shadow-sm"
            @click="scrollRight(tab)"
          >
            <font-awesome-icon icon="chevron-right" />
          </b-button>
        </div>
      </div>
    </div>

    <!-- Full Grid View -->
    <div v-else>
      <div class="d-flex align-items-center mb-4 px-3">
        <b-button
          variant="outline-secondary"
          class="mr-3"
          :title="T.wordsBack"
          @click="setViewAll(null)"
        >
          <font-awesome-icon icon="arrow-left" />
        </b-button>
        <h2 class="m-0">{{ getTabTitle(viewAllCategory) }}</h2>
      </div>

      <template v-if="loading || refreshing">
        <b-row>
          <b-col
            v-for="index in 6"
            :key="`skeleton-${index}`"
            cols="12"
            md="6"
            lg="4"
            class="mb-4"
          >
            <omegaup-contest-skeleton></omegaup-contest-skeleton>
          </b-col>
        </b-row>
      </template>
      <div v-else-if="contestListEmpty" class="empty-category">
        {{ T.contestListEmpty }}
      </div>
      <template v-else>
        <b-row>
          <b-col
            v-for="contestItem in contestList"
            :key="contestItem.contest_id"
            cols="12"
            md="6"
            lg="4"
            class="mb-4"
          >
            <omegaup-contest-card :contest="contestItem">
              <!-- Slots -->
              <template #contest-button-scoreboard>
                <div
                  v-if="
                    viewAllCategory === ContestTab.Current ||
                    viewAllCategory === ContestTab.Future
                  "
                ></div>
              </template>

              <template #text-contest-date>
                <b-card-text v-if="viewAllCategory === ContestTab.Current">
                  <font-awesome-icon icon="calendar-alt" />
                  <a :href="getTimeLink(contestItem.finish_time)">
                    {{
                      ui.formatString(T.contestEndTime, {
                        endDate: finishContestDate(contestItem),
                      })
                    }}
                  </a>
                </b-card-text>
                <b-card-text v-else-if="viewAllCategory === ContestTab.Future">
                  <font-awesome-icon icon="calendar-alt" />
                  <a :href="getTimeLink(contestItem.start_time)">
                    {{
                      ui.formatString(T.contestStartTime, {
                        startDate: startContestDate(contestItem),
                      })
                    }}
                  </a>
                </b-card-text>
                <b-card-text v-else-if="viewAllCategory === ContestTab.Past">
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

              <template #contest-dropdown>
                <div
                  v-if="
                    viewAllCategory === ContestTab.Current ||
                    viewAllCategory === ContestTab.Future
                  "
                ></div>
              </template>

              <template #contest-button-enter>
                <div
                  v-if="
                    viewAllCategory === ContestTab.Future ||
                    viewAllCategory === ContestTab.Past
                  "
                ></div>
              </template>

              <template #contest-enroll-status>
                <div v-if="viewAllCategory === ContestTab.Past"></div>
              </template>

              <template #contest-button-see-details>
                <div v-if="viewAllCategory === ContestTab.Past"></div>
              </template>
            </omegaup-contest-card>
          </b-col>
        </b-row>
      </template>

      <div
        v-if="
          !loading &&
          !contestListEmpty &&
          hasMore &&
          contestList.length > showMoreThreshold
        "
        class="text-center mb-2"
      >
        <button
          class="btn btn-outline-primary w-100"
          :disabled="isScrollLoading"
          @click="loadMoreContests"
        >
          {{ showMoreContestButtonText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
const debounce = (fn: (...args: any[]) => void, waitTime: number) => {
  let timer: number | null = null;
  return (...args: any[]) => {
    if (timer) {
      clearTimeout(timer);
    }
    timer = setTimeout(() => {
      fn(...args);
    }, waitTime) as any;
  };
};

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
import ContestSkeleton from './ContestSkeleton.vue';
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
    'omegaup-contest-skeleton': ContestSkeleton,
    FontAwesomeIcon,
  },
  directives: {
    infiniteScroll,
  },
})
class ArenaContestList extends Vue {
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
  refreshing: boolean = false;
  isScrollLoading: boolean = false;
  hasMore: boolean = true;
  columnsPerRow: number = 3;
  viewAllCategory: ContestTab | null = null;
  scrollPositions: { [key: string]: number } = {};
  maxScrollPositions: { [key: string]: number } = {};

  $refs!: {
    [key: string]: HTMLElement | HTMLElement[];
  };

  titleLinkClass(tab: ContestTab) {
    if (this.currentTab === tab) {
      return ['text-center', 'active-title-link'];
    } else {
      return ['text-center', 'title-link'];
    }
  }

  setViewAll(category: ContestTab | null) {
    this.viewAllCategory = category;
    if (category) {
      this.currentTab = category;
      this.fetchInitialContests();
    } else {
      // Returning to summary, ensure we have data for all
      this.fetchInitialContests();
    }
  }

  getTabTitle(tab: ContestTab | null): string {
    if (!tab) return '';
    switch (tab) {
      case ContestTab.Current:
        return this.T.contestListCurrent;
      case ContestTab.Future:
        return this.T.contestListFuture;
      case ContestTab.Past:
        return this.T.contestListPast;
      default:
        return '';
    }
  }

  getContestsForTab(tab: ContestTab): types.ContestListItem[] {
    switch (tab) {
      case ContestTab.Current:
        return this.contests.current || [];
      case ContestTab.Future:
        return this.contests.future || [];
      case ContestTab.Past:
        return this.contests.past || [];
      default:
        return [];
    }
  }

  onSearchQuery() {
    this.fetchInitialContests();
  }

  onSearchQueryDebounced = debounce(this.onSearchQuery, 300);

  onReset() {
    this.currentQuery = '';
  }
  fetchInitialContests() {
    if (this.viewAllCategory) {
      const urlObj = new URL(window.location.href);
      const params: UrlParams = {
        page: 1,
        tab_name: this.viewAllCategory,
        query: this.currentQuery,
        sort_order: this.currentOrder,
        filter: this.currentFilter,
      };
      Vue.set(this.contests, this.viewAllCategory, []);
      this.currentPage = 1;
      this.hasMore = true;
      this.fetchPage(params, urlObj);
    } else {
      // Fetch all for summary view
      [ContestTab.Current, ContestTab.Future, ContestTab.Past].forEach(
        (tab) => {
          const urlObj = new URL(window.location.href);
          const params: UrlParams = {
            page: 1,
            tab_name: tab,
            query: this.currentQuery,
            sort_order: this.currentOrder,
            filter: this.currentFilter,
          };
          // Only update URL for the Current tab to avoid overwriting it multiple times
          // or setting it to 'past' which might be confusing.
          // Actually, if we are in summary view, we probably don't want to set tab_name in URL at all?
          // But the parent logic sets it based on params.tab_name.
          // Let's just update for Current.
          this.fetchPage(params, urlObj, tab === ContestTab.Current);
        },
      );
    }
  }
  mounted() {
    this.fetchInitialContests();
    this.updateColumnsPerRow();
    window.addEventListener('resize', this.updateColumnsPerRow);
  }

  beforeDestroy() {
    window.removeEventListener('resize', this.updateColumnsPerRow);
  }

  updateColumnsPerRow() {
    if (window.innerWidth >= 992) {
      this.columnsPerRow = 3;
    } else if (window.innerWidth >= 768) {
      this.columnsPerRow = 2;
    } else {
      this.columnsPerRow = 1;
    }
  }

  scrollLeft(tab: ContestTab) {
    const container = (this.$refs[
      `scrollContainer_${tab}`
    ] as HTMLElement[])[0];
    if (container) {
      container.scrollBy({ left: -600, behavior: 'smooth' });
    }
  }

  scrollRight(tab: ContestTab) {
    const container = (this.$refs[
      `scrollContainer_${tab}`
    ] as HTMLElement[])[0];
    if (container) {
      container.scrollBy({ left: 600, behavior: 'smooth' });
    }
  }

  onScroll(tab: ContestTab) {
    const container = (this.$refs[
      `scrollContainer_${tab}`
    ] as HTMLElement[])[0];
    if (container) {
      this.$set(this.scrollPositions, tab, container.scrollLeft);
      this.$set(
        this.maxScrollPositions,
        tab,
        container.scrollWidth - container.clientWidth,
      );
    }
  }

  canScrollLeft(tab: ContestTab): boolean {
    return (this.scrollPositions[tab] || 0) > 0;
  }

  canScrollRight(tab: ContestTab): boolean {
    const contests = this.getContestsForTab(tab);
    if (contests.length <= 3) return false;

    const currentScroll = this.scrollPositions[tab] || 0;
    const maxScroll = this.maxScrollPositions[tab] || 0;
    // If maxScroll is 0, we might not have calculated it yet, so check if we have enough items
    if (maxScroll === 0) {
      return true;
    }
    return currentScroll < maxScroll - 10; // -10 for tolerance
  }

  updated() {
    // Recalculate scroll limits when DOM updates
    [ContestTab.Current, ContestTab.Future, ContestTab.Past].forEach((tab) => {
      this.onScroll(tab);
    });
  }

  async loadMoreContests() {
    if (this.isScrollLoading || !this.hasMore || this.loading) return;

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
    } catch (error) {
      console.error('Error loading more contests:', error);
      // On error, re-enable the button after a delay to prevent spam
      setTimeout(() => {
        this.isScrollLoading = false;
      }, 2000);
      return;
    } finally {
      this.isScrollLoading = false;
    }
  }

  fetchPage(params: UrlParams, urlObj: URL, shouldUpdateUrl: boolean = true) {
    this.$emit('fetch-page', { params, urlObj, shouldUpdateUrl });
    // Turn off refreshing after a short delay to allow parent component to respond
    setTimeout(() => {
      this.refreshing = false;
    }, 1000);
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
  }

  orderByEnds() {
    this.currentOrder = ContestOrder.Ends;
  }

  orderByDuration() {
    this.currentOrder = ContestOrder.Duration;
  }

  orderByOrganizer() {
    this.currentOrder = ContestOrder.Organizer;
  }

  orderByContestants() {
    this.currentOrder = ContestOrder.Contestants;
  }

  orderBySignedUp() {
    this.currentOrder = ContestOrder.SignedUp;
  }

  filterBySignedUp() {
    this.currentFilter = ContestFilter.SignedUp;
  }

  filterByRecommended() {
    this.currentFilter = ContestFilter.OnlyRecommended;
  }

  filterByAll() {
    this.currentFilter = ContestFilter.All;
  }

  get showMoreContestButtonText(): string {
    if (this.isScrollLoading) {
      return T.contestsListLoading;
    }
    return T.contestsListShowMore;
  }

  get showMoreThreshold(): number {
    return this.columnsPerRow * 2;
  }

  get contestList(): types.ContestListItem[] {
    const tab = this.viewAllCategory || this.currentTab;
    switch (tab) {
      case ContestTab.Current:
        return this.contests.current;
      case ContestTab.Past:
        return this.contests.past;
      case ContestTab.Future:
        return this.contests.future;
      default:
        return this.contests.current;
    }
  }

  get contestListEmpty(): boolean {
    if (!this.contestList) return true;
    return this.contestList.length === 0;
  }
  @Watch('currentTab', { immediate: true, deep: true })
  onCurrentTabChanged(newValue: ContestTab, oldValue: undefined | ContestTab) {
    if (typeof oldValue === 'undefined') return;
    this.fetchInitialContests();
  }

  @Watch('currentOrder', { immediate: true, deep: true })
  onCurrentOrderChanged(
    newValue: ContestOrder,
    oldValue: undefined | ContestOrder,
  ) {
    if (typeof oldValue === 'undefined') return;
    this.fetchInitialContests();
  }

  @Watch('currentFilter', { immediate: true, deep: true })
  onCurrentFilterChanged(
    newValue: ContestFilter,
    oldValue: undefined | ContestFilter,
  ) {
    if (typeof oldValue === 'undefined') return;
    this.fetchInitialContests();
  }
}

export default ArenaContestList;
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
  color: var(--arena-reset-text-color);
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
  background: var(
    --arena-submissions-list-skeletonloader-final-background-color
  );
  border-radius: 8px;
  animation: loading 1.5s infinite;
}

.horizontal-scroll-container {
  overflow-x: auto;
  white-space: nowrap;
  -webkit-overflow-scrolling: touch;
  padding-bottom: 1rem;
}

.section-separator {
  border-bottom: 1px solid var(--arena-contest-list-separator-color, #e0e0e0);
  padding-bottom: 2rem;
  margin-bottom: 2rem !important;
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

  .btns-group {
    justify-content: flex-start;

    .dropdown {
      flex: 1;
      gap: 1rem;
      margin-right: 0.8rem;
    }
  }
}

.scroll-wrapper {
  &:hover .scroll-btn {
    opacity: 0.8;
    visibility: visible;
  }
}

.scroll-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  z-index: 2;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  visibility: hidden;
  transition: opacity 0.2s, visibility 0.2s;

  &:hover {
    opacity: 1 !important;
  }
}

.scroll-left {
  left: 10px;
}

.scroll-right {
  right: 10px;
}
</style>
