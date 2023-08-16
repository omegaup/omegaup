<template>
  <b-modal v-model="showModal" hide-footer>
    <template #modal-title>
      <h5 class="modal-title font-weight-bold">
        {{ T.nextRegisteredContestModalTitle }}
      </h5>
    </template>
    <b-container>
      <b-row class="p-1">
        <b-col class="col-12 p-1 text-center">
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
      </b-row>
      <b-row class="p-1 flex-column flex-sm-row">
        <b-col class="col-md-6 col-sm-12 p-1 text-center">
          <font-awesome-icon class="mr-1" icon="clipboard-list" />
          {{ nextRegisteredContest.organizer }}
        </b-col>
        <b-col class="col-md-6 col-sm-12 p-1 text-center">
          <font-awesome-icon class="mr-1" icon="users" />
          {{ nextRegisteredContest.contestants }}
        </b-col>
      </b-row>
      <b-row class="p-1 flex-column flex-sm-row">
        <b-col class="col-md-6 col-sm-12 p-1 text-center">
          <font-awesome-icon icon="calendar-alt" />
          <a :href="startTimeLink">
            {{
              ui.formatString(T.contestStartTime, {
                startDate: startContestDate,
              })
            }}
          </a>
        </b-col>
        <b-col class="col-md-6 col-sm-12 p-1 text-center">
          <font-awesome-icon class="mr-1" icon="stopwatch" />
          {{
            ui.formatString(T.contestDuration, {
              duration: contestDuration,
            })
          }}
        </b-col>
      </b-row>
      <b-row class="p-1 justify-content-center">
        <b-col class="col-md-4 col-sm-12 p-1">
          <button
            type="button"
            class="btn btn-primary w-100"
            data-dismiss="modal"
            @click="onSubmit"
          >
            {{ T.nextRegisteredContestModalButton }}
          </button>
        </b-col>
      </b-row>
    </b-container>
  </b-modal>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import * as time from '../../time';
import * as ui from '../../ui';
import T from '../../lang';

import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
import { ModalPlugin, LayoutPlugin } from 'bootstrap-vue';
Vue.use(ModalPlugin);
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
export default class UserNextRegisteredContest extends Vue {
  @Prop() nextRegisteredContest!: types.ContestListItem | null;
  T = T;
  ui = ui;
  showModal = true;

  get contestDuration(): string {
    return time.formatContestDuration(
      this.nextRegisteredContest.start_time,
      this.nextRegisteredContest.finish_time,
    );
  }

  get startContestDate(): string {
    return this.nextRegisteredContest.start_time.toLocaleDateString();
  }

  get startTimeLink(): string {
    return `http://timeanddate.com/worldclock/fixedtime.html?iso=${this.nextRegisteredContest.start_time.toISOString()}`;
  }

  onSubmit(): void {
    this.showModal = false;
    this.$emit('submit', this.nextRegisteredContest.alias);
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

>>> .modal-dialog {
  max-width: 330px;
}

>>> .modal-header {
  border-bottom: 0;
}
</style>
