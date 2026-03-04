<template>
  <b-card class="mb-2 h-100 shadow-sm contest-card-vertical">
    <div class="d-flex justify-content-between align-items-start mb-2">
      <h5
        class="m-0 font-weight-bold text-truncate w-100"
        :title="contest.title"
      >
        <a
          :href="getContestURL(contest.alias)"
          class="text-dark text-decoration-none"
        >
          {{ contest.title }}
        </a>
      </h5>
      <font-awesome-icon
        v-if="contest.recommended"
        ref="contestIconRecommended"
        class="text-warning ml-2"
        icon="award"
      />
    </div>

    <div class="mb-3 text-muted small">
      <div class="d-flex align-items-center mb-1">
        <font-awesome-icon icon="clipboard-list" class="mr-2" />
        <span class="text-truncate" :title="contest.organizer">{{
          contest.organizer
        }}</span>
      </div>
      <div class="d-flex align-items-center mb-1">
        <slot name="text-contest-date"></slot>
      </div>
      <div class="d-flex align-items-center">
        <font-awesome-icon icon="stopwatch" class="mr-2" />
        <span>{{
          ui.formatString(T.contestDuration, {
            duration: contestDuration,
          })
        }}</span>
      </div>
    </div>

    <div class="mt-auto">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center text-muted">
          <font-awesome-icon icon="users" class="mr-2" />
          <span>{{ contest.contestants }}</span>
        </div>
        <slot name="contest-enroll-status">
          <div
            v-if="contest.participating"
            ref="contestEnrollStatus"
            class="text-success d-flex align-items-center small font-weight-bold"
          >
            <font-awesome-icon class="mr-1" icon="check-circle" />
            {{ T.contestEnrollStatus }}
          </div>
        </slot>
      </div>

      <div class="d-flex flex-column">
        <slot name="contest-button-enter">
          <b-button
            v-if="contest.participating"
            ref="contestButtonEnter"
            :href="getContestURL(contest.alias)"
            variant="primary"
            block
            size="sm"
            class="mb-2"
          >
            {{ T.contestButtonEnter }}
          </b-button>
        </slot>
        <slot name="contest-button-see-details">
          <b-button
            v-if="!contest.participating"
            ref="contestButtonSeeDetails"
            :href="getContestURL(contest.alias)"
            variant="primary"
            block
            size="sm"
            class="mb-2 d-flex align-items-center justify-content-center"
          >
            <font-awesome-icon class="mr-1" icon="sign-in-alt" />
            {{ T.contestButtonSeeDetails }}
          </b-button>
        </slot>
        <slot name="contest-button-scoreboard">
          <b-button
            ref="contestButtonScoreboard"
            :href="getContestScoreboardURL(contest.alias)"
            variant="success"
            block
            size="sm"
            class="mb-2 d-flex align-items-center justify-content-center text-white"
          >
            <font-awesome-icon class="mr-1" icon="table" />
            {{ T.contestButtonScoreboard }}
          </b-button>
        </slot>
        <slot name="contest-button-virtual">
          <b-button
            ref="contestButtonVirtual"
            :href="getVirtualContestURL(contest.alias)"
            variant="primary"
            block
            size="sm"
            class="mb-2 d-flex align-items-center justify-content-center"
          >
            <font-awesome-icon class="mr-1" icon="gamepad" />
            {{ T.contestVirtualMode }}
          </b-button>
        </slot>
        <slot name="contest-button-practice">
          <b-button
            ref="contestButtonPractice"
            :href="getPracticeContestURL(contest.alias)"
            variant="primary"
            block
            size="sm"
            class="mb-2 d-flex align-items-center justify-content-center"
          >
            <font-awesome-icon class="mr-1" icon="flask" />
            {{ T.contestPracticeMode }}
          </b-button>
        </slot>
      </div>
    </div>
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
export default class ContestCardv2 extends Vue {
  @Prop({ required: true }) contest!: types.ContestListItem;

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
.contest-card-vertical {
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
  border: 1px solid var(--contest-card-border-color);

  &:hover {
    transform: translateY(-5px);
    box-shadow: var(--contest-card-hover-box-shadow) !important;
  }
}

.contest-enroll-status {
  color: $omegaup-green;
}

.btn {
  /* Reset max-width from previous styles if necessary, or keep it if desired */
  max-width: none;
}
</style>
