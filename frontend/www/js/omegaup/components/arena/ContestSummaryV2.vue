<template>
  <div class="summary main">
    <h1>{{ ui.contestTitle(contest) }}</h1>
    <omegaup-markdown
      :markdown="(contest && contest.description) || ''"
    ></omegaup-markdown>
    <table class="table table-bordered mx-auto w-50 mb-0">
      <tr v-if="showDeadlines">
        <td>
          <strong>{{ T.arenaPracticeStartTime }}</strong>
        </td>
        <td>{{ time.formatTimestamp(contest.start_time) }}</td>
      </tr>
      <tr v-if="showDeadlines">
        <td>
          <strong>{{ T.arenaPracticeEndtime }}</strong>
        </td>
        <td>
          {{
            contest.finish_time
              ? time.formatTimestamp(contest.finish_time)
              : T.wordsUnlimitedDuration
          }}
        </td>
      </tr>
      <tr
        v-if="
          showRanking &&
          typeof contest.scoreboard === 'number' &&
          duration != Infinity
        "
      >
        <td>
          <strong>{{ T.arenaPracticeScoreboardCutoff }}</strong>
        </td>
        <td>
          {{
            time.formatTimestamp(
              new Date(
                contest.start_time.getTime() +
                  (duration * contest.scoreboard) / 100,
              ),
            )
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
        <td>
          <strong>{{ T.arenaContestOrganizer }}</strong>
        </td>
        <td>
          <a :href="`/profile/${contest.director}/`">{{ contest.director }}</a>
        </td>
      </tr>
    </table>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { omegaup } from '../../omegaup';
import * as ui from '../../ui';
import * as time from '../../time';

import omegaup_Markdown from '../Markdown.vue';

@Component({
  components: {
    'omegaup-markdown': omegaup_Markdown,
  },
})
export default class ContestSummary extends Vue {
  @Prop() contest!: omegaup.Contest;
  @Prop({ default: true }) showDeadlines!: boolean;
  @Prop({ default: true }) showRanking!: boolean;

  T = T;
  ui = ui;
  time = time;

  get duration(): number {
    if (!this.contest.start_time || !this.contest.finish_time) {
      return Infinity;
    }
    return (
      this.contest.finish_time.getTime() - this.contest.start_time.getTime()
    );
  }

  get windowLength(): string {
    if (this.duration === Infinity) {
      return T.wordsUnlimitedDuration;
    }
    if (this.contest.window_length) {
      return time.formatDelta(this.contest.window_length);
    }
    return time.formatDelta(this.duration);
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
