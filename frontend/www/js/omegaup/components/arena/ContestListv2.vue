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
        nav-wrapper-class="contest-list-nav  col-md-2 col-sm-12 test-class"
      >
        <b-card>
          <b-container>
            <b-row class="p-1" align-v="center">
              <b-col class="col-md-6 col-sm-12">
                <form :action="queryURL" method="GET">
                  <div class="input-group">
                    <input
                      v-model.lazy="currentQuery"
                      class="form-control"
                      type="text"
                      name="query"
                      autocomplete="off"
                      autocorrect="off"
                      autocapitalize="off"
                      spellcheck="false"
                      :placeholder="T.wordsKeyword"
                    />
                    <button class="btn" type="reset">&times;</button>
                    <div class="input-group-append">
                      <input
                        class="btn btn-primary btn-md btn-block active"
                        type="submit"
                        :value="T.wordsSearch"
                      />
                    </div>
                  </div>
                </form>
              </b-col>
              <b-col sm="12" class="d-flex col-md-6 btns-group mt-1">
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
                <b-dropdown ref="dropdownFilterBy" class="mr-1" no-caret>
                  <template #button-content>
                    <div>
                      <font-awesome-icon icon="filter" />
                      {{ T.contestFilterBy }}
                    </div>
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
          <div v-if="filteredContestList.length === 0">
            <div class="empty-category">{{ T.contestListEmpty }}</div>
          </div>
          <omegaup-contest-card
            v-for="contestItem in filteredContestList"
            v-else
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
          <div v-if="filteredContestList.length === 0">
            <div class="empty-category">{{ T.contestListEmpty }}</div>
          </div>
          <omegaup-contest-card
            v-for="contestItem in filteredContestList"
            v-else
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
          <div v-if="filteredContestList.length === 0">
            <div class="empty-category">{{ T.contestListEmpty }}</div>
          </div>
          <omegaup-contest-card
            v-for="contestItem in filteredContestList"
            v-else
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
            <template #contest-button-singup>
              <div></div>
            </template>
          </omegaup-contest-card>
        </b-tab>
      </b-tabs>
      <b-pagination-nav
        ref="paginator"
        v-model="currentPage"
        base-url="#"
        first-number
        last-number
        size="lg"
        align="center"
        :link-gen="linkGen"
        :number-of-pages="numberOfPages(currentTab)"
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

@Component({
  components: {
    'omegaup-contest-card': ContestCard,
    FontAwesomeIcon,
  },
})
export default class ArenaContestList extends Vue {
  @Prop({ default: null }) countContests!: { [key: string]: number } | null;
  @Prop() contests!: types.ContestList;
  @Prop() query!: string;
  @Prop() tab!: ContestTab;
  @Prop() sortOrder!: ContestOrder;
  @Prop({ default: ContestFilter.All }) filter!: ContestFilter;
  @Prop() page!: number;
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
    const numberOfPages = Math.ceil(this.countContests[tab] / 10);
    return numberOfPages;
  }

  get queryURL(): string {
    return `/arena/#${this.currentTab}`;
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

  get filteredContestList(): types.ContestListItem[] {
    const filters: Array<(contestItem: types.ContestListItem) => boolean> = [];
    if (this.currentFilter === ContestFilter.SignedUp) {
      filters.push((item) => item.participating);
    }
    if (this.currentFilter === ContestFilter.OnlyRecommended) {
      filters.push((item) => item.recommended);
    }
    return this.sortedContestList.slice().filter((contestItem) => {
      for (const filter of filters) {
        if (!filter(contestItem)) {
          return false;
        }
      }
      return true;
    });
  }

  get sortedContestList(): types.ContestListItem[] {
    function compareNumber(a: number, b: number): number {
      if (a < b) {
        return 1;
      } else if (a > b) {
        return -1;
      }
      return 0;
    }
    let sortBy: (a: types.ContestListItem, b: types.ContestListItem) => number;
    switch (this.currentOrder) {
      case ContestOrder.None:
        return this.contestList.slice();
      case ContestOrder.Title:
        sortBy = (a, b) => a.title.localeCompare(b.title);
        break;
      case ContestOrder.Ends:
        sortBy = (a, b) =>
          compareNumber(a.finish_time.getTime(), b.finish_time.getTime());
        break;
      case ContestOrder.Duration:
        sortBy = (a, b) =>
          compareNumber(
            a.finish_time.getTime() - a.start_time.getTime(),
            b.finish_time.getTime() - b.start_time.getTime(),
          );
        break;
      case ContestOrder.Organizer:
        sortBy = (a, b) => a.organizer.localeCompare(b.organizer);
        break;
      case ContestOrder.Contestants:
        sortBy = (a, b) => compareNumber(a.contestants, b.contestants);
        break;
      case ContestOrder.SignedUp:
        sortBy = (a, b) =>
          compareNumber(a.participating ? 1 : 0, b.participating ? 1 : 0);
        break;
    }
    return this.contestList.slice().sort(sortBy);
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
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

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

.scroll-content {
  overflow-y: auto;
  max-height: 800px;
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
