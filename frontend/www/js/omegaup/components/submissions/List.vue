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
      <div class="panel-body">
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
        <table class="table">
          <thead>
            <tr>
              <th>{{ T.wordsTime }}</th>
              <th>{{ T.wordsUser }}</th>
              <th>{{ T.wordsProblem }}</th>
              <th>{{ T.wordsLanguage }}</th>
              <th>{{ T.wordsVerdict }}</th>
              <th>{{ T.wordsRuntime }}</th>
              <th>{{ T.wordsMemory }}</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import omegaup from '../../api.js';
import UI from '../../ui.js';

@Component
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
}
</script>
