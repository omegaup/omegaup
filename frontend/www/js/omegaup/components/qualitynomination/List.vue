<template>
  <div>
    <div class="form-inline" v-if="!myView">
      <select name="column" class="form-control" v-model="selectColumn">
        <option
          v-for="(columnText, columnIndex) in columns"
          v-bind:value="columnIndex"
        >
          {{ columnText }}</option
        >
      </select>
      <omegaup-autocomplete
        v-bind:init="el => typeahead.problemTypeahead(el)"
        v-model="query"
        v-bind:placeholder="T.wordsKeyword"
        class="form-control"
        v-show="selectColumn == 'alias'"
      ></omegaup-autocomplete>

      <omegaup-autocomplete
        v-bind:init="el => typeahead.userTypeahead(el)"
        v-model="queryUsername"
        v-bind:placeholder="T.wordsKeyword"
        class="form-control"
        v-show="
          selectColumn == 'nominator_username' ||
            selectColumn == 'author_username'
        "
      ></omegaup-autocomplete>
      <button
        class="btn btn-primary"
        v-on:click.prevent="
          $emit(
            'goToPage',
            1,
            showAll ? 'all' : 'open',
            selectColumn == 'nominator_username' ||
              selectColumn == 'author_username'
              ? queryUsername
              : query,
            selectColumn,
          )
        "
      >
        {{ T.wordsSearch }}
      </button>
      <br /><br /><br />
    </div>
    <div class="card">
      <h3 class="card-header">
        {{
          UI.formatString(T.nominationsRangeHeader, {
            lowCount: (pages - 1) * length + 1,
            highCount: pages * length,
          })
        }}
      </h3>
      <div class="card-body">
        <a v-if="isAdmin" href="/group/omegaup:quality-reviewer/edit/#members">
          {{ T.addUsersToReviewerGroup }}
        </a>
        <div class="pull-right" v-if="!myView">
          <label>
            <input
              type="checkbox"
              v-model="showAll"
              v-on:change="
                $emit(
                  'goToPage',
                  1,
                  showAll ? 'all' : 'open',
                  selectColumn == 'nominator_username' ||
                    selectColumn == 'author_username'
                    ? queryUsername
                    : query,
                  selectColumn,
                )
              "
            />
            {{ T.qualityNominationShowAll }}
          </label>
        </div>
      </div>
      <table class="table table-striped">
        <thead>
          <tr>
            <th>{{ T.wordsAlias }}</th>
            <th v-if="!myView">{{ T.wordsNominator }}</th>
            <th>{{ T.wordsAuthor }}</th>
            <th>{{ T.wordsSubmissionDate }}</th>
            <th v-if="!myView">{{ T.wordsReason }}</th>
            <th class="text-center">{{ T.wordsStatus }}</th>
            <th><!-- view button --></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="nomination in nominations">
            <td>
              <a v-bind:href="problemUrl(nomination.problem.alias)">{{
                nomination.problem.title
              }}</a>
            </td>
            <td v-if="!myView">
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
            <td v-if="!myView">{{ nomination.contents.reason }}</td>
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
        v-on:page-changed="
          page =>
            $emit(
              'goToPage',
              page,
              this.showAll
                ? 'all'
                : 'open'(
                    selectColumn == 'nominator_username' ||
                      selectColumn == 'author_username',
                  )
                ? queryUsername
                : query,
              selectColumn,
            )
        "
      ></omegaup-common-paginator>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Emit, Watch } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import * as UI from '../../ui';
import paginador from '../common/Paginatorv2.vue';
import { types } from '../../api_types';
import Autocomplete from '../Autocomplete.vue';
import * as typeahead from '../../typeahead';

@Component({
  components: {
    'omegaup-common-paginator': paginador,
    'omegaup-autocomplete': Autocomplete,
  },
})
export default class QualityNominationList extends Vue {
  @Prop() pages!: number;
  @Prop() length!: number;
  @Prop() myView!: boolean;
  @Prop() nominations!: types.NominationListItem[];
  @Prop() pagerItems!: types.PageItem[];
  @Prop() isAdmin!: boolean;

  showAll = true;
  T = T;
  UI = UI;
  typeahead = typeahead;

  query = '';
  queryUsername = '';
  selectColumn = '';
  columns = {
    alias: T.wordsAlias,
    nominator_username: T.wordsNominator,
    author_username: T.wordsAuthor,
  };

  @Watch('selectColumn')
  onPropertyChanged() {
    this.query = '';
    this.queryUsername = '';
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
