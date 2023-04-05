<template>
  <b-card class="mb-2">
    <b-container>
      <b-row class="p-1 flex-column flex-sm-row" align-v="center">
        <b-col class="col-md-5 col-sm-12 p-1 text-center">
          <b-card-text>
            <h5>
              <a :href="getContestURL(contest.alias)">{{ contest.title }}</a>
              <font-awesome-icon
                v-if="contest.recommended"
                ref="contestIconRecommended"
                class="ml-1"
                icon="award"
              />
            </h5>
          </b-card-text>
        </b-col>
        <b-col class="col-md-3 col-sm-12 p-1 text-center">
          <b-card-text>
            <font-awesome-icon icon="clipboard-list" />
            {{ contest.organizer }}
          </b-card-text>
        </b-col>
        <b-col class="col-md-4 col-sm-12 p-1 text-center">
          <slot name="contest-button-scoreboard">
            <b-button
              ref="contestButtonScoreboard"
              :href="getContestScoreboardURL(contest.alias)"
              variant="success"
            >
              <font-awesome-icon icon="table" />
              {{ T.contestButtonScoreboard }}
            </b-button>
          </slot>
          <slot name="contest-enroll-status">
            <b-card-text
              v-if="contest.participating"
              ref="contestEnrollStatus"
              class="contest-enroll-status"
            >
              <font-awesome-icon icon="clipboard-check" />
              {{ T.contestEnrollStatus }}
            </b-card-text>
          </slot>
        </b-col>
      </b-row>
      <b-row class="p-1 flex-column flex-sm-row" align-v="center">
        <b-col class="col-md-5 col-sm-12 p-1 text-center">
          <slot name="text-contest-date"></slot>
        </b-col>
        <b-col class="col-md-3 col-sm-12 p-1 text-center">
          <b-card-text>
            <font-awesome-icon icon="stopwatch" />
            {{
              ui.formatString(T.contestDuration, {
                duration: contestDuration,
              })
            }}
          </b-card-text>
        </b-col>
        <b-col class="col-md-1 col-sm-12 p-1 text-center">
          <b-card-text>
            <font-awesome-icon icon="users" />
            {{ contest.contestants }}
          </b-card-text>
        </b-col>
        <b-col class="col-md-3 col-sm-12 p-1 text-center">
          <slot name="contest-button-enter">
            <b-button
              v-if="contest.participating"
              ref="contestButtonEnter"
              :href="getContestURL(contest.alias)"
              variant="primary"
            >
              <font-awesome-icon icon="sign-in-alt" />
              {{ T.contestButtonEnter }}
            </b-button>
          </slot>
          <slot name="contest-button-singup">
            <b-button
              v-if="!contest.participating"
              ref="contestButtonSingUp"
              :href="getContestURL(contest.alias)"
              variant="primary"
            >
              <font-awesome-icon icon="sign-in-alt" />
              {{ T.contestButtonSingUp }}
            </b-button>
          </slot>
          <slot name="contest-dropdown">
            <b-dropdown variant="primary">
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
          </slot>
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
library.add(fas);

@Component({
  components: {
    FontAwesomeIcon,
  },
})
export default class ContestCard extends Vue {
  @Prop() contest!: types.ContestListItem;

  T = T;
  ui = ui;

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
    return `/contest/${encodeURIComponent(alias)}/virtual/`;
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
