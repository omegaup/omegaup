<template>
  <div>
    <div class="col-sm-12">
      <h1 class="title">{{ T.wordsContests }}</h1>
    </div>
    <b-card no-body>
      <b-tabs
        v-model="currentTab"
        class="sidebar"
        pills
        card
        vertical
        nav-wrapper-class="contest-list-nav col-sm-4 col-md-2"
      >
        <b-card>
          <b-container>
            <b-row class="p-1" align-v="center">
              <b-col cols="6">
                <form :action="queryURL" method="GET">
                  <div class="input-group">
                    <input
                      v-model="currentQuery"
                      class="form-control"
                      type="text"
                      name="query"
                      autocomplete="off"
                      :placeholder="T.wordsKeyword"
                    />
                    <div class="input-group-append">
                      <input
                        class="btn btn-primary btn-md active"
                        type="submit"
                        :value="T.wordsSearch"
                      />
                    </div>
                  </div>
                </form>
              </b-col>
              <b-col cols="6">
                <b-dropdown ref="dropdownOrderBy" no-caret>
                  <template #button-content>
                    <div>
                      <font-awesome-icon icon="sort-amount-down" />
                      {{ T.contestOrderBy }}
                    </div>
                  </template>
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
              </b-col>
            </b-row>
          </b-container>
        </b-card>
        <b-tab
          ref="currentContestTab"
          :title="T.contestListCurrent"
          :title-link-class="titleLinkClass(ContestTab.Current)"
        >
          <div v-if="contests.current.length === 0">
            <div class="empty-category">{{ T.contestListEmpty }}</div>
          </div>
          <omegaup-contest-card
            v-for="contestItem in sortedContestList"
            v-else
            :key="contestItem.contest_id"
            :contest="contestItem"
            :contest-tab="currentTab"
          >
            <template #contest-button-scoreboard>
              <div></div>
            </template>
            <template #text-contest-date>
              <b-card-text>
                <font-awesome-icon icon="calendar-alt" />
                {{
                  ui.formatString(T.contestEndTime, {
                    endDate: finishContestDate(contestItem),
                  })
                }}
              </b-card-text>
            </template>
            <template #contest-dropdown>
              <div></div>
            </template>
          </omegaup-contest-card>
        </b-tab>
        <b-tab
          ref="futureContestTab"
          :title="T.contestListFuture"
          :title-link-class="titleLinkClass(ContestTab.Future)"
        >
          <div v-if="contests.future.length === 0">
            <div class="empty-category">{{ T.contestListEmpty }}</div>
          </div>
          <omegaup-contest-card
            v-for="contestItem in sortedContestList"
            v-else
            :key="contestItem.contest_id"
            :contest="contestItem"
            :contest-tab="currentTab"
          >
            <template #contest-button-scoreboard>
              <div></div>
            </template>
            <template #text-contest-date>
              <b-card-text>
                <font-awesome-icon icon="calendar-alt" />
                {{
                  ui.formatString(T.contestStartTime, {
                    startDate: startContestDate(contestItem),
                  })
                }}
              </b-card-text>
            </template>
            <template #contest-button-enter>
              <div></div>
            </template>
            <template #contest-dropdown>
              <div></div>
            </template>
          </omegaup-contest-card>
        </b-tab>
        <b-tab
          ref="pastContestTab"
          :title="T.contestListPast"
          :title-link-class="titleLinkClass(ContestTab.Past)"
        >
          <div v-if="contests.past.length === 0">
            <div class="empty-category">{{ T.contestListEmpty }}</div>
          </div>
          <omegaup-contest-card
            v-for="contestItem in sortedContestList"
            v-else
            :key="contestItem.contest_id"
            :contest="contestItem"
            :contest-tab="currentTab"
          >
            <template #contest-enroll-status>
              <div></div>
            </template>
            <template #text-contest-date>
              <b-card-text>
                <font-awesome-icon icon="calendar-alt" />
                {{
                  ui.formatString(T.contestStartedTime, {
                    startedDate: startContestDate(contestItem),
                  })
                }}
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
} from 'bootstrap-vue';
import ContestCard from './ContestCard.vue';
Vue.use(TabsPlugin);
Vue.use(CardPlugin);
Vue.use(DropdownPlugin);
Vue.use(LayoutPlugin);
library.add(fas);

export enum ContestTab {
  Current = 0,
  Future = 1,
  Past = 2,
}

export enum ContestOrder {
  None = -1,
  Title = 0,
  Ends = 1,
  Duration = 2,
  Organizer = 3,
  Contestants = 4,
  SignedUp = 5,
}

@Component({
  components: {
    'omegaup-contest-card': ContestCard,
    FontAwesomeIcon,
  },
})
export default class ArenaContestList extends Vue {
  @Prop() contests!: types.ContestList;
  @Prop() query!: string;
  @Prop() tab!: ContestTab;
  T = T;
  ui = ui;
  ContestTab = ContestTab;
  ContestOrder = ContestOrder;
  currentTab: ContestTab = this.tab;
  currentQuery: string = this.query;
  currentOrder: ContestOrder = ContestOrder.None;

  titleLinkClass(tab: ContestTab) {
    if (this.currentTab === tab) {
      return ['text-center', 'active-title-link'];
    } else {
      return ['text-center', 'title-link'];
    }
  }

  get queryURL(): string {
    return `/arenav2/#${this.currentTab}`;
  }

  finishContestDate(contest: types.ContestListItem): string {
    return contest.finish_time.toLocaleDateString();
  }

  startContestDate(contest: types.ContestListItem): string {
    return contest.start_time.toLocaleDateString();
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
  /deep/ .contest-list-nav {
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
</style>
