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
                <form method="GET">
                  <div class="input-group">
                    <input type="hidden" name="page" :value="currentPage" />
                    <input type="hidden" name="tab_name" :value="currentTab" />
                    <input
                      type="hidden"
                      name="sort_order"
                      :value="currentOrder"
                    />
                    <input type="hidden" name="filter" :value="currentFilter" />
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
                    />
                    <button class="btn reset-btn nav-link" type="reset">
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
                    :href="hrefGen({ sortOrder: ContestOrder.Ends })"
                    data-order-by-ends
                  >
                    <font-awesome-icon
                      v-if="currentOrder === ContestOrder.Ends"
                      icon="check"
                      class="mr-1"
                    />{{ T.contestOrderByEnds }}</b-dropdown-item
                  >
                  <b-dropdown-item
                    :href="hrefGen({ sortOrder: ContestOrder.Title })"
                    data-order-by-title
                  >
                    <font-awesome-icon
                      v-if="currentOrder === ContestOrder.Title"
                      icon="check"
                      class="mr-1"
                    />{{ T.contestOrderByTitle }}</b-dropdown-item
                  >
                  <b-dropdown-item
                    :href="hrefGen({ sortOrder: ContestOrder.Duration })"
                    data-order-by-duration
                  >
                    <font-awesome-icon
                      v-if="currentOrder === ContestOrder.Duration"
                      icon="check"
                      class="mr-1"
                    />{{ T.contestOrderByDuration }}</b-dropdown-item
                  >
                  <b-dropdown-item
                    :href="hrefGen({ sortOrder: ContestOrder.Organizer })"
                    data-order-by-organizer
                  >
                    <font-awesome-icon
                      v-if="currentOrder === ContestOrder.Organizer"
                      icon="check"
                      class="mr-1"
                    />{{ T.contestOrderByOrganizer }}</b-dropdown-item
                  >
                  <b-dropdown-item
                    :href="hrefGen({ sortOrder: ContestOrder.Contestants })"
                    data-order-by-contestants
                  >
                    <font-awesome-icon
                      v-if="currentOrder === ContestOrder.Contestants"
                      icon="check"
                      class="mr-1"
                    />{{ T.contestOrderByContestants }}</b-dropdown-item
                  >
                  <b-dropdown-item
                    :href="hrefGen({ sortOrder: ContestOrder.SignedUp })"
                    data-order-by-signed-up
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
                    :href="hrefGen({ filter: ContestFilter.All })"
                    data-filter-by-all
                  >
                    <font-awesome-icon
                      v-if="currentFilter === ContestFilter.All"
                      icon="check"
                      class="mr-1"
                    />{{ T.contestFilterByAll }}</b-dropdown-item
                  >
                  <b-dropdown-item
                    :href="hrefGen({ filter: ContestFilter.SignedUp })"
                    data-filter-by-signed-up
                  >
                    <font-awesome-icon
                      v-if="currentFilter === ContestFilter.SignedUp"
                      icon="check"
                      class="mr-1"
                    />{{ T.contestFilterBySignedUp }}</b-dropdown-item
                  >
                  <b-dropdown-item
                    :href="hrefGen({ filter: ContestFilter.OnlyRecommended })"
                    data-filter-by-recommended
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
          v-for="tab in tabsContent"
          :key="tab.name"
          :ref="tab.ref"
          class="scroll-content"
          :title="tab.title"
          :title-link-class="titleLinkClass(tab.name)"
          :active="currentTab === tab.name"
          :title-link-attributes="{ href: hrefGen({ tab: tab.name }) }"
          @click="goToPage(tab.name)"
        >
          <div v-if="refreshing" :class="{ line: true }"></div>
          <div v-else-if="contests.length === 0">
            <div class="empty-category">{{ T.contestListEmpty }}</div>
          </div>
          <template v-else>
            <omegaup-contest-card
              v-for="contestItem in contests"
              :key="contestItem.contest_id"
              :contest="contestItem"
            >
              <template
                v-if="tab.name === ContestTab.Past"
                #contest-enroll-status
              >
                <div></div>
              </template>
              <template v-else #contest-button-scoreboard>
                <div></div>
              </template>
              <template #text-contest-date>
                <b-card-text>
                  <font-awesome-icon icon="calendar-alt" />
                  <a :href="getTimeLink(tab.name, contestItem)">
                    {{ getTimeLinkDescription(tab.name, contestItem) }}
                  </a>
                </b-card-text>
              </template>
              <template
                v-if="tab.name === ContestTab.Future"
                #contest-button-enter
              >
                <div></div>
              </template>
              <template
                v-if="tab.name === ContestTab.Past"
                #contest-button-see-details
              >
                <div></div>
              </template>
              <template v-else #contest-dropdown>
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
      </b-tabs>
      <b-pagination-nav
        ref="paginator"
        v-model="currentPage"
        first-number
        last-number
        size="lg"
        align="center"
        :link-gen="linkGen"
        :number-of-pages="numberOfPages"
      ></b-pagination-nav>
    </b-card>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
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

const tabsContent = [
  {
    name: ContestTab.Current,
    ref: 'currentContestTab',
    title: T.contestListCurrent,
  },
  {
    name: ContestTab.Future,
    ref: 'futureContestTab',
    title: T.contestListFuture,
  },
  { name: ContestTab.Past, ref: 'pastContestTab', title: T.contestListPast },
];

@Component({
  components: {
    'omegaup-contest-card': ContestCard,
    FontAwesomeIcon,
  },
})
export default class ArenaContestList extends Vue {
  @Prop({ default: null }) countContests!: number | null;
  @Prop() contests!: types.ContestListItem[];
  @Prop() query!: string;
  @Prop() tab!: ContestTab;
  @Prop() sortOrder!: ContestOrder;
  @Prop({ default: ContestFilter.All }) filter!: ContestFilter;
  @Prop() page!: number;
  T = T;
  ui = ui;
  tabsContent = tabsContent;
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
    }
    return ['text-center', 'title-link'];
  }

  get numberOfPages(): number {
    if (!this.countContests) {
      // Default value when there are no contests in the list
      return 1;
    }
    return Math.ceil(this.countContests / 10);
  }

  linkGen(pageNum: number) {
    return {
      path: `/arena/`,
      query: {
        page: pageNum,
        tab_name: this.currentTab,
        query: this.query,
        sort_order: this.currentOrder,
        filter: this.filter,
      },
    };
  }

  goToPage(tab: ContestTab) {
    this.refreshing = true;
    this.$emit('go-to-page', {
      path: this.hrefGen({ tab }),
    });
  }

  hrefGen({
    filter,
    sortOrder,
    tab,
    query,
  }: {
    filter?: ContestFilter;
    sortOrder?: ContestOrder;
    tab?: ContestTab;
    query?: string;
  }) {
    if (filter === undefined) {
      filter = this.currentFilter;
    }
    if (sortOrder === undefined) {
      sortOrder = this.currentOrder;
    }
    if (tab === undefined) {
      tab = this.currentTab;
    }

    const queryString = new URLSearchParams({
      page: '1', // Reset page to 1 when changing filters
      tab_name: tab,
      sort_order: sortOrder,
      filter,
    });

    if (!query) {
      return `/arena/?${queryString.toString()}`;
    }

    queryString.set('query', query);
    return `/arena/?${queryString.toString()}`;
  }

  finishContestDate(contest: types.ContestListItem): string {
    return contest.finish_time.toLocaleDateString();
  }

  startContestDate(contest: types.ContestListItem): string {
    return contest.start_time.toLocaleDateString();
  }

  getTimeLink(tab: ContestTab, contestItem: types.ContestListItem): string {
    const time =
      tab !== ContestTab.Current
        ? contestItem.finish_time
        : contestItem.start_time;
    const timeToISO = time.toISOString();
    return `http://timeanddate.com/worldclock/fixedtime.html?iso=${timeToISO}`;
  }

  getTimeLinkDescription(
    tab: ContestTab,
    contestItem: types.ContestListItem,
  ): string {
    switch (tab) {
      case ContestTab.Future:
        return ui.formatString(T.contestStartTime, {
          startDate: this.startContestDate(contestItem),
        });
      case ContestTab.Current:
        return ui.formatString(T.contestEndTime, {
          endDate: this.finishContestDate(contestItem),
        });
      case ContestTab.Past:
        return ui.formatString(T.contestStartedTime, {
          startedDate: this.startContestDate(contestItem),
        });
    }
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

.line {
  height: 49px;
  background: var(
    --arena-submissions-list-skeletonloader-initial-background-color
  );
  border-radius: 8px;
  animation: loading 1.5s infinite;
}

@keyframes loading {
  0% {
    background: var(
      --arena-submissions-list-skeletonloader-initial-background-color
    );
  }
  50% {
    background: var(
      --arena-submissions-list-skeletonloader-final-background-color
    );
  }
  100% {
    background: var(
      --arena-submissions-list-skeletonloader-initial-background-color
    );
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
