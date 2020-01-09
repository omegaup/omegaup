<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title">{{ T.qualityNomination }}</h3>
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
    </div>
    <table class="table table-striped">
      <thead>
        <tr>
          <th class="text-center">{{ T.qualityNominationType }}</th>
          <th>{{ T.wordsAlias }}</th>
          <th>{{ T.wordsNominator }}</th>
          <th>{{ T.wordsAuthor }}</th>
          <th>{{ T.wordsSubmissionDate }}</th>
          <th>{{ T.qualityNominationAssignedJudge }}</th>
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
          <td><!-- TODO: Judges aren't returned from the API yet --></td>
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
  @Prop() nominations!: omegaup.Nomination[];
  @Prop() currentUser!: string;
  @Prop() myView!: boolean;

  showAll = true;
  T = T;

  get visibleNominations(): omegaup.Nomination[] {
    if (this.showAll) {
      return this.nominations;
    }
    return this.nominations.filter((nomination: omegaup.Nomination) => {
      return nomination.status === 'open';
    });
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
