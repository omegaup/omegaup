<template>
  <div>
    <div class="col-sm-12">
      <h1 class="title">{{ T.wordsContests }}</h1>
    </div>
    <b-card no-body>
      <b-tabs
        v-model="activeTab"
        class="sidebar"
        pills
        card
        vertical
        nav-wrapper-class="contest-list-nav col-sm-4 col-md-2"
      >
        <b-tab
          ref="currentContestTab"
          :title="T.contestListCurrent"
          :title-link-class="titleLinkClass(ContestTab.Current)"
          active
        >
          <div v-if="contests.current.length === 0">
            <div class="empty-category">{{ T.contestListEmpty }}</div>
          </div>
          <omegaup-contest-card
            v-for="contestItem in contests.current"
            v-else
            :key="contestItem.contest_id"
            :contest="contestItem"
            :contest-tab="activeTab"
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
            :contest-tab="activeTab"
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
            :contest-tab="activeTab"
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
import { TabsPlugin, CardPlugin } from 'bootstrap-vue';
import ContestCard from './ContestCard.vue';
Vue.use(TabsPlugin);
Vue.use(CardPlugin);
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
  T = T;
  ui = ui;
  ContestTab = ContestTab;
  activeTab: ContestTab = ContestTab.Current;

  titleLinkClass(tab: ContestTab) {
    if (this.activeTab === tab) {
      return ['text-center', 'active-title-link'];
    } else {
      return ['text-center', 'title-link'];
    }
  }

  finishContestDate(contest: types.ContestListItem): string {
    return contest.finish_time.toLocaleDateString();
  }

  startContestDate(contest: types.ContestListItem): string {
    return contest.start_time.toLocaleDateString();
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
