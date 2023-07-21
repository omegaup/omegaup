<template>
  <div>
    <form class="form-group">
      <div class="form-row mb-3">
        <label class="col-form-label">{{ T.wordsSearchBy }}</label>
        <div class="col-md-4">
          <select v-model="selectColumn" name="column" class="form-control">
            <option
              v-for="(columnText, columnIndex) in columns"
              :key="columnIndex"
              :value="columnIndex"
            >
              {{ columnText }}
            </option>
          </select>
        </div>
        <div class="col-md-4">
          <omegaup-common-typeahead
            v-show="selectColumn == 'problem_alias'"
            :existing-options="searchResultProblems"
            :value.sync="queryProblem"
            :placeholder="T.wordsKeyword"
            @update-existing-options="
              (query) => $emit('update-search-result-problems', query)
            "
          ></omegaup-common-typeahead>
          <omegaup-common-typeahead
            v-show="
              selectColumn == 'nominator_username' ||
              selectColumn == 'author_username'
            "
            :existing-options="searchResultUsers"
            :value.sync="queryUsername"
            :max-results="10"
            @update-existing-options="
              (query) => $emit('update-search-result-users', query)
            "
          ></omegaup-common-typeahead>
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
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr class="text-nowrap">
              <th>
                {{ T.wordsAlias }}
                <omegaup-common-sort-controls
                  ref="sortControlByTitle"
                  column="title"
                  :sort-order="sortOrder"
                  :column-name="columnName"
                  @apply-filter="onApplyFilter"
                ></omegaup-common-sort-controls>
              </th>
              <th v-if="!myView">{{ T.qualityNominationNominatedBy }}</th>
              <th>{{ T.qualityNominationCreatedBy }}</th>
              <th>
                {{ T.wordsSubmissionDate }}
                <omegaup-common-sort-controls
                  ref="sortControlByTime"
                  column="time"
                  :sort-order="sortOrder"
                  :column-name="columnName"
                  @apply-filter="onApplyFilter"
                ></omegaup-common-sort-controls>
              </th>
              <th v-if="!myView" data-name="reason">{{ T.wordsReason }}</th>
              <th class="text-center">{{ T.wordsStatus }}</th>
              <th><!-- view button --></th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="nomination in orderedNominations"
              :key="`nomination-${nomination.qualitynomination_id}`"
            >
              <td class="align-middle">
                <a :href="problemUrl(nomination.problem.alias)">{{
                  nomination.problem.title
                }}</a>
              </td>
              <td v-if="!myView" class="align-middle">
                <a :href="userUrl(nomination.nominator.username)">{{
                  nomination.nominator.username
                }}</a>
              </td>
              <td class="align-middle">
                <a :href="userUrl(nomination.author.username)">{{
                  nomination.author.username
                }}</a>
              </td>
              <td class="align-middle">
                {{ nomination.time.toLocaleDateString(T.locale) }}
              </td>
              <td v-if="!myView" class="align-middle">
                {{ nomination.contents.reason }}
              </td>
              <td class="text-center align-middle">{{ nomination.status }}</td>
              <td class="align-middle">
                <a
                  :href="nominationDetailsUrl(nomination.qualitynomination_id)"
                  >{{ T.wordsDetails }}</a
                >
              </td>
            </tr>
          </tbody>
        </table>
      </div>
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
import common_Paginator from '../common/Paginator.vue';
import { types } from '../../api_types';
import common_Typeahead from '../common/Typeahead.vue';
import common_SortControls from '../common/SortControls.vue';

@Component({
  components: {
    'omegaup-common-paginator': common_Paginator,
    'omegaup-common-typeahead': common_Typeahead,
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
  @Prop() searchResultUsers!: types.ListItem[];
  @Prop() searchResultProblems!: types.ListItem[];

  showAll = true;
  T = T;
  ui = ui;

  sortOrder: omegaup.SortOrder = omegaup.SortOrder.Ascending;
  columnName = 'title';

  queryProblem: null | types.ListItem = null;
  queryUsername: null | types.ListItem = null;
  selectColumn = '';
  columns = {
    problem_alias: T.wordsProblem,
    nominator_username: T.qualityNominationNominatedBy,
    author_username: T.qualityNominationCreatedBy,
  };

  get orderedNominations(): types.NominationListItem[] {
    const order = this.sortOrder === omegaup.SortOrder.Ascending ? 1 : -1;

    switch (this.columnName) {
      case 'time':
        return this.nominations.sort(
          (a, b) => order * (a.time.getTime() - b.time.getTime()),
        );
      case 'title':
      default:
        return this.nominations.sort(
          (a, b) => order * a.problem.title.localeCompare(b.problem.title),
        );
    }
  }

  @Watch('selectColumn')
  onPropertyChanged() {
    this.queryProblem = null;
    this.queryUsername = null;
  }

  getQuery(): null | string {
    if (
      this.selectColumn == 'nominator_username' ||
      this.selectColumn == 'author_username'
    ) {
      return this.queryUsername?.key ?? null;
    } else {
      return this.queryProblem?.key ?? null;
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
