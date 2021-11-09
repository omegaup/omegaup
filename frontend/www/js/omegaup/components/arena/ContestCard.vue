<template>
  <b-card>
    <b-container>
      <b-row class="p-1" align-v="center">
        <b-col>
          <b-card-text>
            <h5>
              <a :href="getContestURL(contest.alias)">{{ contest.title }}</a>
            </h5>
          </b-card-text>
        </b-col>
        <b-col>
          <b-card-text>
            <font-awesome-icon icon="clipboard-list" />
            {{ contest.organizer }}
          </b-card-text>
        </b-col>
        <b-col cols="3">
          <b-button
            v-if="contestTab === ContestTab.Past"
            ref="contestButtonScoreboard"
            :href="getContestScoreboardURL(contest.alias)"
            variant="success"
          >
            <font-awesome-icon icon="table" />
            {{ T.contestButtonScoreboard }}
          </b-button>
          <b-card-text
            v-else-if="
              (contestTab === ContestTab.Current || contestTab === ContestTab.Future) && contest.participating
            "
            ref="contestEnrollStatus"
            class="contest-enroll-status"
          >
            <font-awesome-icon icon="clipboard-check" />
            {{ T.contestEnrollStatus }}
          </b-card-text>
        </b-col>
      </b-row>
      <b-row class="p-1" align-v="center">
        <b-col>
          <b-card-text v-if="contestTab === ContestTab.Current">
            <font-awesome-icon icon="calendar-alt" />
            {{
              ui.formatString(T.contestEndTime, {
                endDate: finishContestDate,
              })
            }}
          </b-card-text>
          <b-card-text v-else-if="contestTab === ContestTab.Future">
            <font-awesome-icon icon="calendar-alt" />
            {{
              ui.formatString(T.contestStartTime, {
                startDate: startContestDate,
              })
            }}
          </b-card-text>
          <b-card-text v-else-if="contestTab === ContestTab.Past">
            <font-awesome-icon icon="calendar-alt" />
            {{
              ui.formatString(T.contestStartedTime, {
                startedDate: startContestDate,
              })
            }}
          </b-card-text>
        </b-col>
        <b-col>
          <b-card-text>
            <font-awesome-icon icon="stopwatch" />
            {{
              ui.formatString(T.contestDuration, {
                duration: contestDuration,
              })
            }}
          </b-card-text>
        </b-col>
        <b-col>
          <b-card-text>
            <font-awesome-icon icon="users" />
            {{ contest.contestants }}
          </b-card-text>
        </b-col>
        <b-col>
          <b-button
            v-if="contestTab === ContestTab.Current && contest.participating"
            ref="contestButtonEnter"
            :href="getContestURL(contest.alias)"
            variant="primary"
          >
            <font-awesome-icon icon="sign-in-alt" />
            {{ T.contestButtonEnter }}
          </b-button>
          <b-button
            v-else-if="
              (contestTab === ContestTab.Current || contestTab === ContestTab.Future) && !contest.participating
            "
            ref="contestButtonSingUp"
            :href="getContestURL(contest.alias)"
            variant="primary"
          >
            <font-awesome-icon icon="sign-in-alt" />
            {{ T.contestButtonSingUp }}
          </b-button>
          <b-dropdown v-else-if="contestTab === ContestTab.Past" variant="primary">
            <template #button-content>
              <font-awesome-icon icon="sign-in-alt" />
              {{ T.contestButtonEnter }}
            </template>
            <b-dropdown-item :href="getVirtualContestURL(contest.alias)">{{
              T.contestVirtualMode
            }}</b-dropdown-item>
            <b-dropdown-item :href="getPracticeContestURL(contest.alias)">{{
              T.contestPracticeMode
            }}</b-dropdown-item>
          </b-dropdown>
        </b-col>
      </b-row>
    </b-container>
  </b-card>
</template>

<script lang="ts">
import { Vue, Prop, Component } from 'vue-property-decorator';
import { types } from '../../api_types';
import * as time from '../../time';
import * as ui from '../../ui';
import T from '../../lang';

// Import Bootstrap an BootstrapVue CSS files (order is important)
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';

import { ButtonPlugin, DropdownPlugin, LayoutPlugin } from 'bootstrap-vue';
Vue.use(ButtonPlugin);
Vue.use(DropdownPlugin);
Vue.use(LayoutPlugin);

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
import { ContestTab } from './ContestListv2.vue';
library.add(fas);

@Component({
  components: {
    FontAwesomeIcon,
  },
})
export default class ContestCard extends Vue {
  @Prop() contest!: types.ContestListItem;
  @Prop() contestTab!: ContestTab;

  T = T;
  ui = ui;
  ContestTab = ContestTab;

  get finishContestDate(): string {
    return this.contest.finish_time.toLocaleDateString();
  }

  get startContestDate(): string {
    return this.contest.start_time.toLocaleDateString();
  }

  get contestDuration(): string {
    return time.formatContestDuration(
      this.contest.start_time,
      this.contest.finish_time,
    );
  }

  getContestURL(alias: string): string {
    return `/arena/${encodeURIComponent(alias)}/`;
  }

  getContestScoreboardURL(alias: string): string {
    return `/arena/${encodeURIComponent(alias)}/#ranking`;
  }

  getVirtualContestURL(alias: string): string {
    return `/arena/${encodeURIComponent(alias)}/virtual/`;
  }

  getPracticeContestURL(alias: string): string {
    return `/arena/${encodeURIComponent(alias)}/practice/`;
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
.contest-enroll-status {
  color: $omegaup-green;
}

.btn {
  color: $omegaup-white;
}
</style>
