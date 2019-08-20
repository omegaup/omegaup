<template>
  <div class="panel">
    <div class="page-header">
      <h1>{{ T.qualityNomination }}</h1>
    </div>
    <div class="pull-right"
         v-if="!myView">
      <label><input type="checkbox"
             v-model="showAll"> {{ T.qualityNominationShowAll }}</label>
    </div>
    <div>
      <table class="table table-striped">
        <thead>
          <tr>
            <td>{{ T.qualityNominationType }}</td>
            <td>{{ T.wordsAlias }}</td>
            <td>{{ T.wordsNominator }}</td>
            <td>{{ T.wordsAuthor }}</td>
            <td>{{ T.wordsSubmissionDate }}</td>
            <td>{{ T.qualityNominationAssignedJudge }}</td>
            <td>{{ T.wordsStatus }}</td>
            <td><!-- view button --></td>
          </tr>
        </thead>
        <tbody>
          <tr v-for="nomination in visibleNominations">
            <td>{{ nomination.nomination }}</td>
            <td>
              <a v-bind:href="problemUrl(nomination.problem.alias)">{{ nomination.problem.title
              }}</a>
            </td>
            <td>
              <a v-bind:href="userUrl(nomination.nominator.username)">{{
              nomination.nominator.username }}</a>
            </td>
            <td>
              <a v-bind:href="userUrl(nomination.author.username)">{{ nomination.author.username
              }}</a>
            </td>
            <td>{{ nomination.time.format('long') }}</td>
            <td><!-- TODO: Judges aren't returned from the API yet --></td>
            <td>{{ nomination.status }}</td>
            <td>
              <a v-bind:href="nominationDetailsUrl(nomination.qualitynomination_id)">{{
              T.wordsDetails }}</a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div>
      <a href="/group/omegaup:quality-reviewer/edit/#members">{{ T.addUsersToReviewerGroup }}</a>
    </div>
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
    return '/arena/problem/' + problemAlias + '/';
  }

  userUrl(username: string): string {
    return '/profile/' + username + '/';
  }

  nominationDetailsUrl(nominationId: number): string {
    return '/nomination/' + nominationId + '/';
  }
}

</script>
