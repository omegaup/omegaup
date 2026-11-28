<template>
  <div class="summary main">
    <h1>{{ title }}</h1>
    <omegaup-markdown :markdown="eventDescription"></omegaup-markdown>
    <table
      v-if="finishTime !== null"
      class="table table-bordered mx-auto w-50 mb-0"
    >
      <tr v-if="showDeadlines">
        <td>
          <strong>{{ T.arenaPracticeStartTime }}</strong>
        </td>
        <td>{{ time.formatTimestamp(startTime) }}</td>
      </tr>
      <tr v-if="showDeadlines">
        <td>
          <strong>{{ T.arenaPracticeEndtime }}</strong>
        </td>
        <td>
          {{
            finishTime
              ? time.formatTimestamp(finishTime)
              : T.wordsUnlimitedDuration
          }}
        </td>
      </tr>
      <tr
        v-if="
          showRanking && typeof scoreboard === 'number' && duration != Infinity
        "
      >
        <td>
          <strong>{{ T.arenaPracticeScoreboardCutoff }}</strong>
        </td>
        <td>
          {{
            time.formatTimestamp(
              new Date(startTime.getTime() + (duration * scoreboard) / 100),
            )
          }}
        </td>
      </tr>
      <tr>
        <td>
          <strong>{{ T.arenaContestWindowLength }}</strong>
        </td>
        <td>{{ eventWindowLength }}</td>
      </tr>
      <tr>
        <td>
          <strong>{{ T.arenaContestOrganizer }}</strong>
        </td>
        <td>
          <a :href="`/profile/${admin}/`">{{ admin }}</a>
        </td>
      </tr>
    </table>
    <div v-if="showLogs" class="problem-change-log mt-3">
      <h2>{{ T.arenaContestProblemChangeLog }}</h2>
      <table class="table table-bordered mx-auto w-50 mb-0">
        <thead>
          <tr>
            <th>{{ T.wordsTime }}</th>
            <th>{{ T.wordsActions }}</th>
            <th>{{ T.wordsChangedBy }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="logs.length === 0">
            <td colspan="3" class="text-center text-muted">
              {{ T.wordsEmpty }}
            </td>
          </tr>
          <tr v-for="(log, index) in logs" :key="index">
            <td>{{ time.formatDateTime(log.timestamp) }}</td>
            <td>
              <omegaup-markdown
                class="problem-change-log-message"
                :markdown="formatLogMessage(log.change_type, log.problemAlias)"
              ></omegaup-markdown>
            </td>
            <td>{{ log.changedBy }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';
import * as time from '../../time';

import omegaup_Markdown from '../Markdown.vue';

export default defineComponent({
  name: 'Summary',
  components: {
    'omegaup-markdown': omegaup_Markdown,
  },
  props: {
    title: {
      type: String,
      required: true,
    },
    description: {
      type: String,
      required: true,
    },
    startTime: {
      type: Date as PropType<Date>,
      required: true,
    },
    finishTime: {
      type: Date as PropType<null | Date>,
      default: null,
    },
    scoreboard: {
      type: Number as PropType<null | number>,
      default: null,
    },
    windowLength: {
      type: Number as PropType<null | number>,
      default: null,
    },
    admin: {
      type: String,
      required: true,
    },
    showDeadlines: {
      type: Boolean,
      default: true,
    },
    showRanking: {
      type: Boolean,
      default: true,
    },
    showLogs: {
      type: Boolean,
      default: false,
    },
    logs: {
      type: Array as PropType<types.ContestProblemChangeLog[]>,
      default: () => [],
    },
  },
  data() {
    return {
      T,
      ui,
      time,
    };
  },
  computed: {
    duration(): number {
      if (!this.startTime || !this.finishTime) {
        return Infinity;
      }
      return this.finishTime.getTime() - this.startTime.getTime();
    },
    eventWindowLength(): string {
      if (this.duration === Infinity) {
        return T.wordsUnlimitedDuration;
      }
      if (this.windowLength) {
        // Convert minutes to milliseconds
        return time.formatDelta(this.windowLength * (60 * 1000));
      }
      return time.formatDelta(this.duration);
    },
    eventDescription(): string {
      return this.description || '';
    },
  },
  methods: {
    formatLogMessage(changeType: string, problemAlias: string): string {
      switch (changeType) {
        case 'added':
          return ui.formatString(T.arenaContestProblemAdded, { problemAlias });
        case 'modified':
          return ui.formatString(T.arenaContestProblemModified, {
            problemAlias,
          });
        case 'removed':
          return ui.formatString(T.arenaContestProblemRemoved, {
            problemAlias,
          });
        default:
          return `${changeType}: ${problemAlias}`;
      }
    },
  },
});
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.summary {
  background: var(--arena-summary-background-color);
  padding: 1em;
}

h1 {
  margin: 1em auto 1em auto;
  font-size: 1.5em;
}

h2 {
  margin: 1em auto 0.75em auto;
  font-size: 1.2em;
}

.problem-change-log-message>>p {
  margin-bottom: 0;
}

@media only screen and (min-width: 960px) {
  .summary {
    margin-top: -1.5em;
    margin-right: -1em;
  }
}
</style>
