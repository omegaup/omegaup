<template>
  <div class="summary main">
    <h1>{{ assignment.name }}</h1>
    <omegaup-markdown :markdown="assignmentDescription"></omegaup-markdown>
    <table class="table table-bordered mx-auto w-50 mb-0">
      <tr v-if="showDeadlines">
        <td>
          <strong>{{ T.arenaPracticeStartTime }}</strong>
        </td>
        <td>{{ time.formatTimestamp(assignment.start_time) }}</td>
      </tr>
      <tr v-if="showDeadlines">
        <td>
          <strong>{{ T.arenaPracticeEndtime }}</strong>
        </td>
        <td>
          {{
            assignment.finish_time
              ? time.formatTimestamp(assignment.finish_time)
              : T.wordsUnlimitedDuration
          }}
        </td>
      </tr>
      <tr>
        <td>
          <strong>{{ T.arenaContestWindowLength }}</strong>
        </td>
        <td>{{ windowLength }}</td>
      </tr>
      <tr>
        <!-- <td>
          <strong>{{ T.arenaContestOrganizer }}</strong>
        </td>
        <td>
          <a :href="`/profile/${contest.director}/`">{{ contest.director }}</a>
        </td> -->
      </tr>
    </table>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';
import * as ui from '../../ui';
import * as time from '../../time';

import omegaup_Markdown from '../Markdown.vue';

@Component({
  components: {
    'omegaup-markdown': omegaup_Markdown,
  },
})
export default class AssignmentSummary extends Vue {
  @Prop() assignment!: types.ArenaAssignment;
  @Prop({ default: true }) showDeadlines!: boolean;
  @Prop({ default: true }) showRanking!: boolean;

  T = T;
  ui = ui;
  time = time;

  get duration(): number {
    if (!this.assignment.start_time || !this.assignment.finish_time) {
      return Infinity;
    }
    return (
      this.assignment.finish_time.getTime() - this.assignment.start_time.getTime()
    );
  }

  get windowLength(): string {
    if (this.duration === Infinity) {
      return T.wordsUnlimitedDuration;
    }
    return time.formatDelta(this.duration);
  }

  get assignmentDescription(): string {
    return this.assignment?.description || '';
  }
}
</script>

<style lang="scss" scoped>
.summary {
  background: #fff;
  padding: 1em;
  margin-top: -1.5em;
  margin-right: -1em;
}

h1 {
  margin: 1em auto 1em auto;
  font-size: 1.5em;
}
</style>
