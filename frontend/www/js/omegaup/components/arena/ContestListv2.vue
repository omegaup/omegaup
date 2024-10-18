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
                      v-model="currentQuery"
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
                        type="button"
                        :value="T.wordsSearch"
                        @click.prevent="onSearchQuery"
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
          <div v-if="contestListEmpty">
            <div class="empty-category">{{ T.contestListEmpty }}</div>
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
          <b-spinner
            v-if="refreshing"
            class="spinner mt-4"
            variant="primary"
          ></b-spinner>
        </b-tab>
        <b-tab
          ref="futureContestTab"
          class="scroll-content"
          :title="T.contestListFuture"
          :title-link-class="titleLinkClass(ContestTab.Future)"
          :active="currentTab === ContestTab.Future"
          @click="currentTab = ContestTab.Future"
        >
          <div v-if="contestListEmpty">
            <div class="empty-category">{{ T.contestListEmpty }}</div>
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
          <b-spinner
            v-if="refreshing"
            class="spinner mt-4"
            variant="primary"
          ></b-spinner>
        </b-tab>
        <b-tab
          ref="pastContestTab"
          class="scroll-content"
          :title="T.contestListPast"
          :title-link-class="titleLinkClass(ContestTab.Past)"
          :active="currentTab === ContestTab.Past"
          @click="currentTab = ContestTab.Past"
        >
          <div v-if="contestListEmpty">
            <div class="empty-category">{{ T.contestListEmpty }}</div>
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
        </b-tab>
      </b-tabs>
      <b-pagination-nav
        ref="paginator"
        v-model="currentPage"
        base-url="#"
        first-number
        last-number
        size="lg"
        :align="'center'"
        :link-gen="linkGen"
        :number-of-pages="numberOfPages(currentTab)"
      ></b-pagination-nav>
    </b-card>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch, Ref } from 'vue-property-decorator';
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
  PaginationNavPlugin,
} from 'bootstrap-vue';
import ContestCard from './ContestCard.vue';
Vue.use(TabsPlugin);
Vue.use(CardPlugin);
Vue.use(DropdownPlugin);
Vue.use(LayoutPlugin);
Vue.use(PaginationNavPlugin);
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
})
export default class ArenaContestList extends Vue {
  @Ref('paginator') readonly paginator!: Vue;
  @Prop({ default: null }) countContests!: { [key: string]: number } | null;
  @Prop() contests!: types.ContestList;
  @Prop() query!: string;
  @Prop() tab!: ContestTab;
  @Prop({ default: ContestOrder.None }) sortOrder!: ContestOrder;
  @Prop({ default: ContestFilter.All }) filter!: ContestFilter;
  @Prop() page!: number;
  @Prop({ default: 10 }) pageSize!: number;
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

  titleLinkClass(tab: ContestTab) {
    if (this.currentTab === tab) {
      return ['text-center', 'active-title-link'];
    } else {
      return ['text-center', 'title-link'];
    }
  }

  numberOfPages(tab: ContestTab): number {
    if (!this.countContests || !this.countContests[tab]) {
      // Default value when there are no contests in the list
      return 1;
    }
    const numberOfPages = Math.ceil(this.countContests[tab] / this.pageSize);
    return numberOfPages;
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
    this.fetchPage(params, urlObj);
  }

  onReset() {
    this.currentQuery = '';
  }

  linkGen(pageNum: number) {
    const urlObj = new URL(window.location.href);
    return {
      path: `/arena/`,
      query: {
        page: pageNum,
        tab_name: urlObj.searchParams.get('tab_name') || ContestTab.Current,
        query: urlObj.searchParams.get('query') || '',
        sort_order: urlObj.searchParams.get('sort_order') || ContestOrder.None,
        filter: urlObj.searchParams.get('filter') || ContestFilter.All,
      },
    };
  }

  mounted() {
    (this.paginator.$el as HTMLElement).addEventListener(
      'click',
      this.handlePageClick,
    );
  }

  beforeDestroy() {
    (this.paginator.$el as HTMLElement).removeEventListener(
      'click',
      this.handlePageClick,
    );
  }

  fetchPage(params: UrlParams, urlObj: URL) {
    this.$emit('fetch-page', { params, urlObj });
  }

  handlePageClick(event: MouseEvent) {
    event.preventDefault();
    event.stopPropagation();
    const url = (event.target as HTMLAnchorElement).href;
    if (url) {
      const urlObj = new URL(url);
      const params: UrlParams = {
        page: parseInt(urlObj.searchParams.get('page') || '1', 10),
        tab_name:
          (urlObj.searchParams.get('tab_name') as ContestTab) ||
          ContestTab.Current,
        query: urlObj.searchParams.get('query') || '',
        sort_order:
          (urlObj.searchParams.get('sort_order') as ContestOrder) ||
          ContestOrder.None,
        filter:
          (urlObj.searchParams.get('filter') as ContestFilter) ||
          ContestFilter.All,
      };
      this.fetchPage(params, urlObj);
    }
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
    this.$forceUpdate();
  }

  orderByEnds() {
    this.currentOrder = ContestOrder.Ends;
    this.$forceUpdate();
  }

  orderByDuration() {
    this.currentOrder = ContestOrder.Duration;
    this.$forceUpdate();
  }

  orderByOrganizer() {
    this.currentOrder = ContestOrder.Organizer;
    this.$forceUpdate();
  }

  orderByContestants() {
    this.currentOrder = ContestOrder.Contestants;
    this.$forceUpdate();
  }

  orderBySignedUp() {
    this.currentOrder = ContestOrder.SignedUp;
    this.$forceUpdate();
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

  get contestList(): types.ContestListItem[] {
    switch (this.currentTab) {
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

  @Watch('currentOrder', { immediate: true, deep: true })
  onCurrentOrderChanged(
    newValue: ContestOrder,
    oldValue: undefined | ContestOrder,
  ) {
    if (typeof oldValue === 'undefined') return;
    const urlObj = new URL(window.location.href);
    const params: UrlParams = {
      page: 1,
      tab_name:
        (urlObj.searchParams.get('tab_name') as ContestTab) ||
        ContestTab.Current,
      query: urlObj.searchParams.get('query') || '',
      sort_order: newValue,
      filter:
        (urlObj.searchParams.get('filter') as ContestFilter) ||
        ContestFilter.All,
    };
    this.currentPage = 1;
    this.fetchPage(params, urlObj);
  }

  @Watch('currentFilter', { immediate: true, deep: true })
  onCurrentFilterChanged(
    newValue: ContestFilter,
    oldValue: undefined | ContestFilter,
  ) {
    if (typeof oldValue === 'undefined') return;
    const urlObj = new URL(window.location.href);
    const params: UrlParams = {
      page: 1,
      tab_name:
        (urlObj.searchParams.get('tab_name') as ContestTab) ||
        ContestTab.Current,
      query: urlObj.searchParams.get('query') || '',
      sort_order:
        (urlObj.searchParams.get('sort_order') as ContestOrder) ||
        ContestOrder.None,
      filter: newValue,
    };
    this.currentPage = 1;
    this.fetchPage(params, urlObj);
  }

  @Watch('currentTab', { immediate: true, deep: true })
  onCurrentTabChanged(newValue: ContestTab, oldValue: undefined | ContestTab) {
    if (typeof oldValue === 'undefined') return;
    const urlObj = new URL(window.location.href);
    const params: UrlParams = {
      page: 1,
      tab_name: newValue,
      query: urlObj.searchParams.get('query') || '',
      sort_order:
        (urlObj.searchParams.get('sort_order') as ContestOrder) ||
        ContestOrder.None,
      filter:
        (urlObj.searchParams.get('filter') as ContestFilter) ||
        ContestFilter.All,
    };
    this.currentPage = 1;
    this.fetchPage(params, urlObj);
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

.sidebar {
  >>> .contest-list-nav {
    background-color: var(
      --arena-contest-list-sidebar-tab-list-background-color
    );

    .active-title-link {
      background-color: var(
        --arena-contest-list-sidebar-tab-list-link-background-color--active
      ) !important;
    }

    .title-link {
      color: var(
        --arena-contest-list-sidebar-tab-list-link-font-color
      ) !important;
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
