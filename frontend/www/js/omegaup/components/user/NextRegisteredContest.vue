<template>
  <div v-show="showContestInfo" class="p-4 border bg-light container-fluid">
    <div class="justify-content-end">
      <button type="button" class="btn-close" @click="showContestInfo = false">
        ×
      </button>
    </div>
    <div class="container">
      <div class="row p-1">
        <div class="col-12 p-1 text-center">
          <h3 class="mb-3 display-4">
            {{ T.userNextRegisteredContestTitle }}
          </h3>
        </div>
      </div>
      <div class="row p-1 flex-column flex-sm-row align-items-center">
        <div class="col-md-4 col-sm-12 p-1 text-center">
          <h5 class="m-0">
            <a>{{ nextRegisteredContest.title }}</a>
            <font-awesome-icon
              v-if="nextRegisteredContest.recommended"
              ref="contestIconRecommended"
              class="ms-1"
              icon="award"
            />
          </h5>
        </div>
        <div class="col-md-4 col-sm-12 p-1 text-center">
          <font-awesome-icon class="me-1" icon="clipboard-list" />
          {{ nextRegisteredContest.organizer }}
        </div>
        <div class="col-md-4 col-sm-12 p-1 text-center">
          <font-awesome-icon class="me-1" icon="users" />
          {{ nextRegisteredContest.contestants }}
        </div>
      </div>
      <div class="row p-1 flex-column flex-sm-row align-items-center">
        <div class="col-md-4 col-sm-12 p-1 text-center">
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
        </div>
        <div class="col-md-4 col-sm-12 p-1 text-center">
          <font-awesome-icon class="me-1" icon="stopwatch" />
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
        </div>
        <div class="col-md-4 col-sm-12 p-1 text-center">
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
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import * as time from '../../time';
import * as ui from '../../ui';
import T from '../../lang';
import omegaup_Countdown from '../Countdown.vue';
import { omegaup } from '../../omegaup';
import { getExternalUrl } from '../../urlHelper';

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
    return `${getExternalUrl(
      'TimeAndDateBaseURL',
    )}?iso=${this.nextRegisteredContest.start_time.toISOString()}`;
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
