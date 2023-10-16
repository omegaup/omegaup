<template>
  <div class="contest panel">
    <div class="panel-body">
      <div class="text-center">
        <h2>{{ ui.formatString(T.virtualTitle, { title: title }) }}</h2>
        <span>{{ time.formatDelta(finishTime - startTime) }}</span>
        <form class="form" @submit.prevent="onSubmit">
          <div class="row">
            <div class="form-group col-md-4"></div>
            <div class="form-group col-md-4">
              <label>{{ T.contestNewFormStartDate }}</label>
              <omegaup-datetimepicker
                v-model="virtualContestStartTime"
              ></omegaup-datetimepicker>
            </div>
            <div class="form-group col-md-4"></div>
          </div>
          <button
            class="btn btn-primary"
            type="submit"
            data-schedule-virtual-button
          >
            {{ T.contestNewFormScheduleVirtualContest }}
          </button>
        </form>
      </div>
      <hr />
      <div class="">
        <h1>{{ T.registerForContestChallenges }}</h1>
        <p>{{ description }}</p>
      </div>
      <div class="">
        <h1>{{ T.registerForContestRules }}</h1>
        <ul>
          <li>
            {{
              ui.formatString(T.contestIntroScoreboardTimePercent, {
                window_length: scoreboard,
              })
            }}
          </li>
          <li>
            {{
              ui.formatString(T.contestIntroSubmissionsSeparationDesc, {
                window_length: Math.floor(submissionsGap / 60),
              })
            }}
          </li>
        </ul>
      </div>
    </div>
    <!-- div contest-details -->
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import * as ui from '../../ui';
import * as time from '../../time';
import DateTimePicker from '../DateTimePicker.vue';

@Component({
  components: {
    'omegaup-datetimepicker': DateTimePicker,
  },
})
export default class ArenaVirtual extends Vue {
  @Prop() title!: string;
  @Prop() description!: string;
  @Prop() startTime!: Date;
  @Prop() finishTime!: Date;
  @Prop() scoreboard!: string;
  @Prop() submissionsGap!: number;

  T = T;
  ui = ui;
  time = time;
  virtualContestStartTime = new Date();

  onSubmit(): void {
    this.$emit('submit', {
      virtualContestStartTime: this.virtualContestStartTime,
    });
  }
}
</script>
