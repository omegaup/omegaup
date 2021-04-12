<template>
  <div>
    <form class="form-group">
      <div class="form-row mb-3">
        <label class="col-form-label">{{ T.wordsSearchBy }}</label>
        <div class="col-md-4">
          <select v-model="selectColumn" name="column" class="form-control">
            <option
              v-for="(columnText, columnIndex) in columns"
              :value="columnIndex"
            >
              {{ columnText }}
            </option>
          </select>
        </div>
        <div class="col-md-4">
          <omegaup-autocomplete
            v-show="selectColumn == 'problem_alias'"
            v-model="queryProblem"
            :init="(el) => typeahead.problemTypeahead(el)"
            :placeholder="T.wordsKeyword"
            class="form-control"
          ></omegaup-autocomplete>
          <omegaup-autocomplete
            v-show="
              selectColumn == 'nominator_username' ||
              selectColumn == 'author_username'
            "
            v-model="queryUsername"
            :init="(el) => typeahead.userTypeahead(el)"
            :placeholder="T.wordsKeyword"
            class="form-control"
          ></omegaup-autocomplete>
        </div>
      </div>
      <button
        class="btn btn-primary"
        @click.prevent="
          $emit('go-to-page', 1, getStatus(), getQuery(), selectColumn)
        "
      >
        {{ T.wordsSearch }}
      </button>
    </form>
    <div class="card">
      <h3 class="card-header">
        {{
          ui.formatString(T.nominationsRangeHeader, {
            lowCount: (pages - 1) * length + 1,
            highCount: pages * length,
          })
        }}
      </h3>
      <div class="card-body">
        <a v-if="isAdmin" href="/group/omegaup:quality-reviewer/edit/#members">
          {{ T.addUsersToReviewerGroup }}
        </a>
        <div v-if="!myView" class="pull-right">
          <label>
            <input
              v-model="showAll"
              type="checkbox"
              @change="
                $emit('go-to-page', 1, getStatus(), getQuery(), selectColumn)
              "
            />
            {{ T.qualityNominationShowAll }}
          </label>
        </div>
      </div>
      <table class="table table-striped">
        <thead>
          <tr>
            <th>
              <span>
                {{ T.wordsAlias }}
                <omegaup-common-sort-controls
                  ref="sortControlByTitle"
                  column="title"
                  :sort-order="sortOrder"
                  :column-name="columnName"
                  @apply-filter="onApplyFilter"
                ></omegaup-common-sort-controls>
              </span>
            </th>
            <th v-if="!myView">{{ T.wordsNominator }}</th>
            <th>{{ T.wordsAuthor }}</th>
            <th>
              <span>
                {{ T.wordsSubmissionDate }}
                <omegaup-common-sort-controls
                  ref="sortControlByTime"
                  column="time"
                  :sort-order="sortOrder"
                  :column-name="columnName"
                  @apply-filter="onApplyFilter"
                ></omegaup-common-sort-controls>
              </span>
            </th>
            <th v-if="!myView" data-name="reason">{{ T.wordsReason }}</th>
            <th class="text-center">{{ T.wordsStatus }}</th>
            <th><!-- view button --></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="nomination in orderedNominations">
            <td>
              <a :href="problemUrl(nomination.problem.alias)">{{
                nomination.problem.title
              }}</a>
            </td>
            <td v-if="!myView">
              <a :href="userUrl(nomination.nominator.username)">{{
                nomination.nominator.username
              }}</a>
            </td>
            <td>
              <a :href="userUrl(nomination.author.username)">{{
                nomination.author.username
              }}</a>
            </td>
            <td>{{ nomination.time.toLocaleDateString(T.locale) }}</td>
            <td v-if="!myView">{{ nomination.contents.reason }}</td>
            <td class="text-center">{{ nomination.status }}</td>
            <td>
              <a
                :href="nominationDetailsUrl(nomination.qualitynomination_id)"
                >{{ T.wordsDetails }}</a
              >
            </td>
          </tr>
        </tbody>
      </table>
      <omegaup-common-paginator
        :pager-items="pagerItems"
        @page-changed="
          (page) =>
            $emit('go-to-page', page, getStatus(), getQuery(), selectColumn)
        "
      ></omegaup-common-paginator>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import * as ui from '../../ui';
import common_Paginator from '../common/Paginatorv2.vue';
import { types } from '../../api_types';
import Autocomplete from '../Autocomplete.vue';
import * as typeahead from '../../typeahead';
import common_SortControls from '../common/SortControls.vue';

@Component({
  components: {
    'omegaup-common-paginator': common_Paginator,
    'omegaup-autocomplete': Autocomplete,
    'omegaup-common-sort-controls': common_SortControls,
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
  ui = ui;
  typeahead = typeahead;

  sortOrder: omegaup.SortOrder = omegaup.SortOrder.Ascending;
  columnName = 'title';

  queryProblem = '';
  queryUsername = '';
  selectColumn = '';
  columns = {
    problem_alias: T.wordsProblem,
    nominator_username: T.wordsNominator,
    author_username: T.wordsAuthor,
  };

  get orderedNominations() : types.NominationListItem[] {
    let order = this.sortOrder === omegaup.SortOrder.Ascending ? -1 : 1;
    
    switch (this.columnName) {
      case 'title':
        return this.nominations.sort((a, b) => 
          a.problem.title < b.problem.title ? order : (
            b.problem.title < a.problem.title ? (-1 * order) : 0
          )
        );
      case 'time':
        return this.nominations.sort((a, b) => 
          a.time.getTime() < b.time.getTime() ? order : (
            b.time.getTime() < a.time.getTime() ? (-1 * order) : 0
          )
        );
      default:
        return this.nominations.sort((a, b) => 
          a.problem.title < b.problem.title ? -1 : (
            b.problem.title < a.problem.title ? 1 : 0
          )
        );
    }
  }

  @Watch('selectColumn')
  onPropertyChanged() {
    this.queryProblem = '';
    this.queryUsername = '';
  }

  getQuery(): string {
    if (
      this.selectColumn == 'nominator_username' ||
      this.selectColumn == 'author_username'
    ) {
      return this.queryUsername;
    } else {
      return this.queryProblem;
    }
  }

  getStatus(): string {
    if (this.showAll) {
      return 'all';
    } else {
      return 'open';
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

  onApplyFilter(columnName: string, sortOrder: string): void {
    this.columnName = columnName;

    this.sortOrder =
      sortOrder === omegaup.SortOrder.Ascending
        ? omegaup.SortOrder.Ascending
        : omegaup.SortOrder.Descending;
  }
}
</script>
