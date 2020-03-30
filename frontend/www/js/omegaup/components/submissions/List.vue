<template>
  <div class="row">
    <div class="page-header text-center">
      <h1>
        {{ T.submissionsListTitle }}
      </h1>
      <h4 v-if="!includeUser && submissions.length > 0">
        {{ T.wordsBy }}
        <omegaup-username
          v-bind:username="submissions[0].username"
          v-bind:classname="submissions[0].classname"
          v-bind:linkify="true"
        ></omegaup-username>
      </h4>
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
      <div class="panel-body" v-if="includeUser || showControls">
        <template v-if="includeUser">
          <label
            ><omegaup-autocomplete
              class="form-control"
              v-bind:init="el =&gt; UI.userTypeahead(el)"
              v-model="searchedUsername"
            ></omegaup-autocomplete
          ></label>
          <a
            class="btn btn-primary"
            type="button"
            v-bind:href="
              `/submissions/${encodeURIComponent(this.searchedUsername)}/`
            "
          >
            {{ T.searchUser }}
          </a>
        </template>
        <template v-if="showControls">
          <template v-if="page > 1">
            <a class="prev" v-bind:href="`/submissions/?page=${page - 1}`">
              {{ T.wordsPrevPage }}</a
            >
            <span class="delimiter" v-show="showNextPage">|</span>
          </template>
          <a
            class="next"
            v-show="showNextPage"
            v-bind:href="`/submissions/?page=${page + 1}`"
            >{{ T.wordsNextPage }}
          </a>
        </template>
      </div>
      <table class="table submissions-table">
        <thead>
          <tr>
            <th class="text-center">{{ T.wordsTime }}</th>
            <th class="text-center" v-if="includeUser">{{ T.wordsUser }}</th>
            <th class="text-center">{{ T.wordsProblem }}</th>
            <th
              v-bind:class="{ 'fixed-width-column': includeUser }"
              class="text-center"
            >
              {{ T.wordsLanguage }}
            </th>
            <th class="text-center fixed-with-column">{{ T.wordsVerdict }}</th>
            <th class="numericColumn">{{ T.wordsRuntime }}</th>
            <th class="numericColumn">{{ T.wordsMemory }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="submission in submissions">
            <td class="text-center">
              {{ UI.formatDateTime(submission.time) }}
            </td>
            <td class="text-center" v-if="includeUser">
              <omegaup-username
                v-bind:username="submission.username"
                v-bind:classname="submission.classname"
                v-bind:linkify="true"
              >
              </omegaup-username>
              <br />
              <a
                class="school-text"
                v-bind:href="`/schools/profile/${submission.school_id}/`"
                >{{ submission.school_name }}</a
              >
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
      <div class="panel-footer" v-if="showControls">
        <template v-if="page > 1">
          <a class="prev" v-bind:href="`/submissions/?page=${page - 1}`">
            {{ T.wordsPrevPage }}</a
          >
          <span class="delimiter" v-show="showNextPage">|</span>
        </template>
        <a
          class="next"
          v-show="showNextPage"
          v-bind:href="`/submissions/?page=${page + 1}`"
          >{{ T.wordsNextPage }}
        </a>
      </div>
    </div>
  </div>
</template>

<style>
table.submissions-table > tbody > tr > td {
  vertical-align: middle;
}
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

.school-text {
  font-size: 0.9em;
}

.fixed-width-column {
  width: 180px;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import * as UI from '../../ui';
import UserName from '../user/Username.vue';
import Autocomplete from '../Autocomplete.vue';

@Component({
  components: {
    'omegaup-username': UserName,
    'omegaup-autocomplete': Autocomplete,
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
  searchedUsername = '';

  get showNextPage(): boolean {
    return this.length * this.page < this.totalRows;
  }

  get showControls(): boolean {
    return this.showNextPage || this.page > 1;
  }
}
</script>
