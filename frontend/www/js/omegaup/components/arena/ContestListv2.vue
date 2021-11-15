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
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
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
            </div>
            <div class="col-md-6">
              <b-dropdown no-caret>
                <template #button-content>
                  <div>
                    <font-awesome-icon icon="sort-amount-down" />
                    {{ T.contestOrderBy }}
                  </div>
                </template>
                <b-dropdown-item href="#" @click="orderByName">
                  <font-awesome-icon
                    v-if="currentOrder === 0"
                    icon="check"
                    class="mr-1"
                  />{{ T.contestOrderByName }}</b-dropdown-item
                >
                <b-dropdown-item href="#" @click="orderByEnds">
                  <font-awesome-icon
                    v-if="currentOrder === 1"
                    icon="check"
                    class="mr-1"
                  />{{ T.contestOrderByEnds }}</b-dropdown-item
                >
                <b-dropdown-item href="#" @click="orderByDuration">
                  <font-awesome-icon
                    v-if="currentOrder === 2"
                    icon="check"
                    class="mr-1"
                  />{{ T.contestOrderByDuration }}</b-dropdown-item
                >
                <b-dropdown-item href="#" @click="orderByOrganizer">
                  <font-awesome-icon
                    v-if="currentOrder === 3"
                    icon="check"
                    class="mr-1"
                  />{{ T.contestOrderByOrganizer }}</b-dropdown-item
                >
                <b-dropdown-item href="#" @click="orderByContestants">
                  <font-awesome-icon
                    v-if="currentOrder === 4"
                    icon="check"
                    class="mr-1"
                  />{{ T.contestOrderByContestants }}</b-dropdown-item
                >
                <b-dropdown-item href="#" @click="orderBySignedUp">
                  <font-awesome-icon
                    v-if="currentOrder === 5"
                    icon="check"
                    class="mr-1"
                  />{{ T.contestOrderBySignedUp }}</b-dropdown-item
                >
              </b-dropdown>
            </div>
          </div>
        </div>
        <b-tab
          ref="currentContestTab"
          :title="T.contestListCurrent"
          :title-link-class="titleLinkClass(ContestTab.Current)"
        >
          <div v-if="contests.current.length === 0">
            <div class="empty-category">{{ T.contestListEmpty }}</div>
          </div>
          <omegaup-contest-card
            v-for="contestItem in contests.current"
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
            v-for="contestItem in contests.future"
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
            v-for="contestItem in contests.past"
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
import { TabsPlugin, CardPlugin, DropdownPlugin } from 'bootstrap-vue';
import ContestCard from './ContestCard.vue';
Vue.use(TabsPlugin);
Vue.use(CardPlugin);
Vue.use(DropdownPlugin);
library.add(fas);

export enum ContestTab {
  Current = 0,
  Future = 1,
  Past = 2,
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
  currentTab: ContestTab = this.tab;
  currentQuery: string = this.query;
  currentOrder: number = -1;

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

  orderByName() {
    this.currentOrder = 0;
    switch (this.currentTab) {
      case ContestTab.Current:
        this.contests.current.sort((a, b) => (a.title < b.title ? -1 : 1));
        break;
      case ContestTab.Future:
        this.contests.future.sort((a, b) => (a.title < b.title ? -1 : 1));
        break;
      case ContestTab.Past:
        this.contests.past.sort((a, b) => (a.title < b.title ? -1 : 1));
        break;
    }
  }

  orderByEnds() {
    this.currentOrder = 1;
    switch (this.currentTab) {
      case ContestTab.Current:
        this.contests.current.sort((a, b) =>
          a.finish_time < b.finish_time ? -1 : 1,
        );
        break;
      case ContestTab.Future:
        this.contests.future.sort((a, b) =>
          a.finish_time < b.finish_time ? -1 : 1,
        );
        break;
      case ContestTab.Past:
        this.contests.past.sort((a, b) =>
          a.finish_time < b.finish_time ? -1 : 1,
        );
        break;
    }
  }

  orderByDuration() {
    this.currentOrder = 2;
    switch (this.currentTab) {
      case ContestTab.Current:
        this.contests.current.sort((a, b) =>
          a.finish_time.getTime() - a.start_time.getTime() <
          b.finish_time.getTime() - a.start_time.getTime()
            ? -1
            : 1,
        );
        break;
      case ContestTab.Future:
        this.contests.future.sort((a, b) =>
          a.finish_time.getTime() - a.start_time.getTime() <
          b.finish_time.getTime() - a.start_time.getTime()
            ? -1
            : 1,
        );
        break;
      case ContestTab.Past:
        this.contests.past.sort((a, b) =>
          a.finish_time.getTime() - a.start_time.getTime() <
          b.finish_time.getTime() - a.start_time.getTime()
            ? -1
            : 1,
        );
        break;
    }
  }

  orderByOrganizer() {
    this.currentOrder = 3;
    switch (this.currentTab) {
      case ContestTab.Current:
        this.contests.current.sort((a, b) =>
          a.organizer < b.organizer ? -1 : 1,
        );
        break;
      case ContestTab.Future:
        this.contests.future.sort((a, b) =>
          a.organizer < b.organizer ? -1 : 1,
        );
        break;
      case ContestTab.Past:
        this.contests.past.sort((a, b) => (a.organizer < b.organizer ? -1 : 1));
        break;
    }
  }

  orderByContestants() {
    this.currentOrder = 4;
    switch (this.currentTab) {
      case ContestTab.Current:
        this.contests.current.sort((a, b) =>
          a.contestants < b.contestants ? -1 : 1,
        );
        break;
      case ContestTab.Future:
        this.contests.future.sort((a, b) =>
          a.contestants < b.contestants ? -1 : 1,
        );
        break;
      case ContestTab.Past:
        this.contests.past.sort((a, b) =>
          a.contestants < b.contestants ? -1 : 1,
        );
        break;
    }
  }

  orderBySignedUp() {
    this.currentOrder = 5;
    switch (this.currentTab) {
      case ContestTab.Current:
        this.contests.current.sort((a, b) =>
          a.participating > b.participating ? -1 : 1,
        );
        break;
      case ContestTab.Future:
        this.contests.future.sort((a, b) =>
          a.participating > b.participating ? -1 : 1,
        );
        break;
      case ContestTab.Past:
        this.contests.past.sort((a, b) =>
          a.participating > b.participating ? -1 : 1,
        );
        break;
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
