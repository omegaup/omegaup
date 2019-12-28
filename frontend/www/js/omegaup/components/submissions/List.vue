<template>
  <div class="row">
    <div class="page-header">
      <h1 class="text-center">
        {{ T.submissionsListTitle }}
      </h1>
    </div>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title">
          {{
            UI.formatString(T.submissionsRangeHeader, {
              lowCount: (page - 1) * length + 1,
              highCount: page * length,
            })
          }}
        </h3>
      </div>
      <div class="panel-body" v-if="shouldShowControls">
        <template v-if="page > 1">
          <a class="prev" v-bind:href="`/submissions?page=${page - 1}`">
            {{ T.wordsPrevPage }}</a
          >
          <span class="delimiter" v-show="shouldShowNextPage">|</span>
        </template>
        <a
          class="next"
          v-show="shouldShowNextPage"
          v-bind:href="`/submissions?page=${page + 1}`"
          >{{ T.wordsNextPage }}
        </a>
      </div>
      <table class="table">
        <thead>
          <tr>
            <th class="text-center">{{ T.wordsTime }}</th>
            <th class="text-center">{{ T.wordsUser }}</th>
            <th class="text-center">{{ T.wordsProblem }}</th>
            <th class="text-center">{{ T.wordsLanguage }}</th>
            <th class="text-center">{{ T.wordsVerdict }}</th>
            <th class="numericColumn">{{ T.wordsRuntime }}</th>
            <th class="numericColumn">{{ T.wordsMemory }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="submission in submissions">
            <td class="text-center">
              {{ UI.formatDateTime(submission.time) }}
            </td>
            <td class="text-center">
              <omegaup-username
                v-bind:username="submission.username"
                v-bind:classname="submission.classname"
                v-bind:linkify="true"
              >
              </omegaup-username>
            </td>
            <td class="text-center">
              <a v-bind:href="`/arena/problem/${submission.alias}/`">{{
                submission.title
              }}</a>
            </td>
            <td class="text-center">{{ submission.language }}</td>
            <td
              class="text-center verdict"
              v-bind:class="`verdict-${submission.verdict}`"
            >
              {{ T[`verdict${submission.verdict}`] }}
            </td>
            <td class="numericColumn">
              {{
                submission.runtime === 0
                  ? '—'
                  : UI.formatString(T.submissionRunTimeInSeconds, {
                      value: (
                        parseFloat(submission.runtime || '0') / 1000
                      ).toFixed(2),
                    })
              }}
            </td>
            <td class="numericColumn">
              {{
                submission.memory === 0
                  ? '—'
                  : UI.formatString(T.submissionMemoryInMegabytes, {
                      value: (
                        parseFloat(submission.memory) /
                        (1024 * 1024)
                      ).toFixed(2),
                    })
              }}
            </td>
          </tr>
        </tbody>
      </table>
      <div class="panel-footer" v-if="shouldShowControls">
        <template v-if="page > 1">
          <a class="prev" v-bind:href="`/submissions?page=${page - 1}`">
            {{ T.wordsPrevPage }}</a
          >
          <span class="delimiter" v-show="shouldShowNextPage">|</span>
        </template>
        <a
          class="next"
          v-show="shouldShowNextPage"
          v-bind:href="`/submissions?page=${page + 1}`"
          >{{ T.wordsNextPage }}
        </a>
      </div>
    </div>
  </div>
</template>

<style>
.verdict-AC {
  background: #cf6;
}

.verdict-CE {
  background: #f90;
}

.verdict-JE,
.verdict-VE {
  background: #f00;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import omegaup from '../../api.js';
import UI from '../../ui.js';
import UserName from '../user/Username.vue';

@Component({
  components: {
    'omegaup-username': UserName,
  },
})
export default class SubmissionsList extends Vue {
  @Prop() page!: number;
  @Prop() length!: number;
  @Prop() includeUser!: boolean;
  @Prop() totalRows!: number;
  @Prop() submissions!: omegaup.Submission[];

  T = T;
  UI = UI;

  get shouldShowNextPage(): boolean {
    return this.length * this.page < this.totalRows;
  }

  get shouldShowControls(): boolean {
    return this.shouldShowNextPage || this.page > 1;
  }
}
</script>
