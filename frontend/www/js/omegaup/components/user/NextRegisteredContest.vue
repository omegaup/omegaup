<template>
  <b-collapse
    v-model="showContestInfo"
    class="p-4 border bg-light container-fluid"
  >
    <div class="justify-content-end">
      <button type="button" class="close" @click="showContestInfo = false">
        ×
      </button>
    </div>
    <b-container>
      <b-row class="p-1">
        <b-col class="col-12 p-1 text-center">
          <h3 class="mb-3 display-4">
            {{ T.userNextRegisteredContestTitle }}
          </h3>
        </b-col>
      </b-row>
      <b-row class="p-1 flex-column flex-sm-row" align-v="center">
        <b-col class="col-md-4 col-sm-12 p-1 text-center">
          <h5 class="m-0">
            <a>{{ nextRegisteredContest.title }}</a>
            <font-awesome-icon
              v-if="nextRegisteredContest.recommended"
              ref="contestIconRecommended"
              class="ml-1"
              icon="award"
            />
          </h5>
        </b-col>
        <b-col class="col-md-4 col-sm-12 p-1 text-center">
          <font-awesome-icon class="mr-1" icon="clipboard-list" />
          {{ nextRegisteredContest.organizer }}
        </b-col>
        <b-col class="col-md-4 col-sm-12 p-1 text-center">
          <font-awesome-icon class="mr-1" icon="users" />
          {{ nextRegisteredContest.contestants }}
        </b-col>
      </b-row>
      <b-row class="p-1 flex-column flex-sm-row" align-v="center">
        <b-col class="col-md-4 col-sm-12 p-1 text-center">
          <font-awesome-icon icon="calendar-alt" />
          <a v-if="isContestStarted" :href="startTimeLink">
            {{
              ui.formatString(T.contestStartedTime, {
                startedDate: startContestDate,
              })
            }}
          </a>
          <a v-else :href="startTimeLink">
            {{
              ui.formatString(T.contestStartTime, {
                startDate: startContestDate,
              })
            }}
          </a>
        </b-col>
        <b-col class="col-md-4 col-sm-12 p-1 text-center">
          <font-awesome-icon class="mr-1" icon="stopwatch" />
          {{ T.wordsDuration }}:
          <omegaup-countdown
            v-if="isContestStarted"
            class="clock"
            :target-time="nextRegisteredContest.finish_time"
            @finish="now = new Date()"
          ></omegaup-countdown>
          <p v-else class="d-inline">
            {{ contestDuration }}
          </p>
        </b-col>
        <b-col class="col-md-4 col-sm-12 p-1 text-center">
          <button
            v-if="isContestStarted"
            type="button"
            class="btn btn-primary w-75"
            @click="onClick"
          >
            {{ T.userNextRegisteredContestButtonEnter }}
          </button>
          <button
            v-else
            type="button"
            class="btn btn-primary w-75"
            @click="onClick"
          >
            {{ T.userNextRegisteredContestButtonSeeDetails }}
          </button>
        </b-col>
      </b-row>
    </b-container>
  </b-collapse>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import * as time from '../../time';
import * as ui from '../../ui';
import T from '../../lang';
import omegaup_Countdown from '../Countdown.vue';
import { omegaup } from '../../omegaup';

import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';

import { LayoutPlugin, CollapsePlugin } from 'bootstrap-vue';
Vue.use(LayoutPlugin);
Vue.use(CollapsePlugin);

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
library.add(fas);

@Component({
  components: {
    FontAwesomeIcon,
    'omegaup-countdown': omegaup_Countdown,
  },
})
export default class UserNextRegisteredContest extends Vue {
  @Prop() nextRegisteredContest!: types.ContestListItem;
  T = T;
  ui = ui;
  omegaup = omegaup;
  showContestInfo = true;
  now = new Date();

  get contestDuration(): string {
    return time.formatContestDuration(
      this.nextRegisteredContest.start_time,
      this.nextRegisteredContest.finish_time,
    );
  }

  get startContestDate(): string {
    return `${this.nextRegisteredContest.start_time.toLocaleDateString()} ${this.nextRegisteredContest.start_time.toLocaleTimeString()}`;
  }

  get startTimeLink(): string {
    return `https://timeanddate.com/worldclock/fixedtime.html?iso=${this.nextRegisteredContest.start_time.toISOString()}`;
  }

  get isContestStarted(): boolean {
    return this.nextRegisteredContest.start_time < this.now;
  }

  onClick(): void {
    this.showContestInfo = false;
    this.$emit('redirect', this.nextRegisteredContest.alias);
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

h3.display-4 {
  color: $omegaup-primary--darker;
  font-weight: normal;
  font-size: 1.8rem;
  margin-top: 1em;
}
</style>
