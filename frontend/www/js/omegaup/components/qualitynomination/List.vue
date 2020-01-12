<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title">
        {{
          UI.formatString(T.nominationsRangeHeader, {
            lowCount: (page - 1) * length + 1,
            highCount: page * length,
          })
        }}
      </h3>
    </div>
    <div class="panel-body">
      <a href="/group/omegaup:quality-reviewer/edit/#members">
        {{ T.addUsersToReviewerGroup }}
      </a>
      <div class="pull-right" v-if="!myView">
        <label>
          <input type="checkbox" v-model="showAll" />
          {{ T.qualityNominationShowAll }}
        </label>
      </div>
      <div v-if="showControls">
        <template v-if="page > 1">
          <a class="prev" v-bind:href="prevPageUrl"> {{ T.wordsPrevPage }}</a>
          <span class="delimiter" v-show="showNextPage">|</span>
        </template>
        <a class="next" v-show="showNextPage" v-bind:href="nextPageUrl"
          >{{ T.wordsNextPage }}
        </a>
      </div>
    </div>
    <table class="table table-striped">
      <thead>
        <tr>
          <th class="text-center">{{ T.qualityNominationType }}</th>
          <th>{{ T.wordsAlias }}</th>
          <th>{{ T.wordsNominator }}</th>
          <th>{{ T.wordsAuthor }}</th>
          <th>{{ T.wordsSubmissionDate }}</th>
          <th class="text-center">{{ T.wordsStatus }}</th>
          <th><!-- view button --></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="nomination in visibleNominations">
          <td class="text-center">{{ nomination.nomination }}</td>
          <td>
            <a v-bind:href="problemUrl(nomination.problem.alias)">{{
              nomination.problem.title
            }}</a>
          </td>
          <td>
            <a v-bind:href="userUrl(nomination.nominator.username)">{{
              nomination.nominator.username
            }}</a>
          </td>
          <td>
            <a v-bind:href="userUrl(nomination.author.username)">{{
              nomination.author.username
            }}</a>
          </td>
          <td>{{ nomination.time.format('long') }}</td>
          <td class="text-center">{{ nomination.status }}</td>
          <td>
            <a
              v-bind:href="
                nominationDetailsUrl(nomination.qualitynomination_id)
              "
              >{{ T.wordsDetails }}</a
            >
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import omegaup from '../../api.js';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';

@Component
export default class QualityNominationList extends Vue {
  @Prop() page!: number;
  @Prop() length!: number;
  @Prop() myView!: boolean;
  @Prop() nominations!: omegaup.Nomination[];
  @Prop() totalRows!: number;

  showAll = true;
  T = T;
  UI = UI;

  get visibleNominations(): omegaup.Nomination[] {
    if (this.showAll) {
      return this.nominations;
    }
    return this.nominations.filter((nomination: omegaup.Nomination) => {
      return nomination.status === 'open';
    });
  }

  get showNextPage(): boolean {
    return this.length * this.page < this.totalRows;
  }

  get showControls(): boolean {
    return this.showNextPage || this.page > 1;
  }

  get nextPageUrl(): string {
    if (this.myView) {
      return `/nomination/mine/?page=${this.page + 1}`;
    } else {
      return `/nomination/?page=${this.page + 1}`;
    }
  }

  get prevPageUrl(): string {
    if (this.myView) {
      return `/nomination/mine/?page=${this.page - 1}`;
    } else {
      return `/nomination/?page=${this.page - 1}`;
    }
  }

  problemUrl(problemAlias: string): string {
    return `/arena/problem/${problemAlias}/`;
  }

  userUrl(username: string): string {
    return `/profile/${username}/`;
  }

  nominationDetailsUrl(nominationId: number): string {
    return `/nomination/${nominationId}/`;
  }
}
</script>
