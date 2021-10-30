<template>
  <b-card>
    <b-container>
      <b-row align-v="center">
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
            :href="getContestScoreboardURL(contest.alias)"
            variant="success"
            v-if="contestTab === 2"
          >
            <font-awesome-icon icon="table" />
            {{ T.contestButtonScoreBoard }}
          </b-button>
          <b-card-text
            class="contest-enroll-status"
            v-else-if="
              (contestTab === 0 || contestTab === 1) && contest.participating
            "
          >
            <font-awesome-icon icon="clipboard-check" />
            {{ T.contestEnrollStatus }}
          </b-card-text>
        </b-col>
      </b-row>
      <b-row align-v="center">
        <b-col>
          <b-card-text v-if="contestTab === 0">
            <font-awesome-icon icon="calendar-alt" />
            {{
              ui.formatString(T.contestEndTime, {
                endDate: finishContestDate,
              })
            }}
          </b-card-text>
          <b-card-text v-else-if="contestTab === 1">
            <font-awesome-icon icon="calendar-alt" />
            {{
              ui.formatString(T.contestStartTime, {
                startDate: startContestDate,
              })
            }}
          </b-card-text>
          <b-card-text v-else-if="contestTab === 2">
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
                duration: contestDuration(contest.duration),
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
            :href="getContestURL(contest.alias)"
            variant="primary"
            v-if="contestTab === 0 && contest.participating"
          >
            <font-awesome-icon icon="sign-in-alt" />
            {{ T.contestButtonEnter }}
          </b-button>
          <b-button
            :href="getContestURL(contest.alias)"
            variant="primary"
            v-else-if="
              (contestTab === 0 || contestTab === 1) && !contest.participating
            "
          >
            <font-awesome-icon icon="sign-in-alt" />
            {{ T.contestButtonSingUp }}
          </b-button>
          <b-dropdown variant="primary" v-else-if="contestTab === 2">
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

  get finishContestDate(): String {
    return this.contest.finish_time.toLocaleDateString();
  }

  get startContestDate(): String {
    return this.contest.start_time.toLocaleDateString();
  }

  getContestURL(alias: String): String {
    return `arena/${alias}`;
  }

  getContestScoreboardURL(alias: String): String {
    return `arena/${alias}/#ranking`;
  }

  getVirtualContestURL(alias: String): String {
    return `arena/${alias}/virtual`;
  }

  getPracticeContestURL(alias: String): String {
    return `arena/${alias}/practice`;
  }

  contestDuration(seconds: number): String {
    return new Date(seconds * 1000).toISOString().substr(11, 8);
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.row {
  padding: 5px;
}

.contest-enroll-status {
  color: #468847;
}

.btn {
  color: $omegaup-white;
}
</style>
