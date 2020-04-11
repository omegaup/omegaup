<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title">
        {{
          UI.formatString(T.nominationsRangeHeader, {
            lowCount: (pages - 1) * length + 1,
            highCount: pages * length,
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
    <omegaup-common-paginator
      v-bind:pager-items="pagerItems"
      v-on:page-changed="page => $emit('goToPage', page)"
    ></omegaup-common-paginator>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Emit } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import * as UI from '../../ui';
import paginador from '../common/Paginator.vue';
import { types } from '../../api_types';

@Component({
  components: {
    'omegaup-common-paginator': paginador,
  },
})
export default class QualityNominationList extends Vue {
  @Prop() pages!: number;
  @Prop() length!: number;
  @Prop() myView!: boolean;
  @Prop() nominations!: omegaup.Nomination[];
  @Prop() pagerItems!: types.PageItem[];

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
