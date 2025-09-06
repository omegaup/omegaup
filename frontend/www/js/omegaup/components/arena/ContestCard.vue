<template>
  <b-card class="mb-2">
    <b-container>
      <b-row class="p-1 flex-column flex-sm-row" align-v="center">
        <b-col class="col-md-5 col-sm-12 p-1 text-center">
          <b-card-text>
            <h5 class="m-0">
              <a :href="getContestURL(contest.alias)">{{ contest.title }}</a>
              <font-awesome-icon
                v-if="contest.recommended"
                ref="contestIconRecommended"
                class="ml-1 mr-1"
                icon="award"
              />
            </h5>
          </b-card-text>
        </b-col>
        <b-col class="col-md-3 col-sm-12 p-1 text-center">
          <b-card-text class="d-flex justify-content-center align-items-center">
            <font-awesome-icon class="mr-1" icon="clipboard-list" />
            <p class="m-0">{{ contest.organizer }}</p>
          </b-card-text>
        </b-col>

        <b-col class="col-md-4 col-sm-12 p-1 text-center">
          <slot name="contest-button-scoreboard">
            <b-button
              ref="contestButtonScoreboard"
              :href="getContestScoreboardURL(contest.alias)"
              variant="success"
              class="d-flex justify-content-center align-items-center"
            >
              <font-awesome-icon class="mr-1" icon="table" />
              <p class="m-0">{{ T.contestButtonScoreboard }}</p>
            </b-button>
          </slot>

          <div class="d-flex align-items-center justify-content-center">
            <slot name="contest-enroll-status">
              <b-card-text
                v-if="contest.participating"
                ref="contestEnrollStatus"
                class="contest-enroll-status d-flex justify-content-center align-items-center"
              >
                <font-awesome-icon class="mr-1" icon="clipboard-check" />
                <p class="m-0">{{ T.contestEnrollStatus }}</p>
              </b-card-text>
            </slot>
          </div>
        </b-col>
      </b-row>
      <b-row class="p-1 flex-column flex-sm-row" align-v="center">
        <b-col class="col-md-5 col-sm-12 p-1 text-center">
          <slot name="text-contest-date"></slot>
        </b-col>
        <b-col class="col-md-3 col-sm-12 p-1 text-center">
          <b-card-text class="d-flex justify-content-center align-items-center">
            <font-awesome-icon class="mr-1" icon="stopwatch" />
            <p class="m-0">
              {{
                ui.formatString(T.contestDuration, {
                  duration: contestDuration,
                })
              }}
            </p>
          </b-card-text>
        </b-col>
        <b-col
          class="col-md-4 col-sm-12 p-1 text-center d-flex justify-content-center"
        >
          <div class="d-flex align-items-center justify-content-center">
            <slot>
              <b-card-text
                class="mr-3 m-0 d-flex justify-content-center align-items-center"
              >
                <font-awesome-icon icon="users" class="m-1" />
                <p class="m-0">{{ contest.contestants }}</p>
              </b-card-text>
            </slot>
            <slot name="contest-button-enter">
              <b-button
                v-if="contest.participating"
                ref="contestButtonEnter"
                :href="getContestURL(contest.alias)"
                variant="primary"
                class="button-style d-flex justify-content-center align-items-center"
              >
                <font-awesome-icon class="mr-1" icon="sign-in-alt" />
                {{ T.contestButtonEnter }}
              </b-button>
            </slot>
            <slot name="contest-button-see-details">
              <b-button
                v-if="!contest.participating"
                ref="contestButtonSeeDetails"
                :href="getContestURL(contest.alias)"
                variant="primary"
                class="d-flex align-items-center justify-content-center"
              >
                <font-awesome-icon class="mr-1" icon="sign-in-alt" />
                {{ T.contestButtonSeeDetails }}
              </b-button>
            </slot>
          </div>
          <slot name="contest-dropdown">
            <b-dropdown variant="primary" class="d-inline-block">
              <template #button-content>
                <font-awesome-icon class="mr-1" icon="sign-in-alt" />
                {{ T.contestButtonEnter }}
              </template>
              <b-dropdown-item :href="getVirtualContestURL(contest.alias)">
                {{ T.contestVirtualMode }}
              </b-dropdown-item>
              <b-dropdown-item :href="getPracticeContestURL(contest.alias)">
                {{ T.contestPracticeMode }}
              </b-dropdown-item>
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
.button-style {
  padding: 0.5rem 1rem;
}
.btn {
  color: $omegaup-white;
}
</style>
