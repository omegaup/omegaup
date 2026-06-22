<template>
  <div class="card" data-course-problemlist>
    <div class="card-header">
      <h5>
        {{ addCardHeaderTitleLabel }}
      </h5>
      <span>{{ addCardHeaderDescLabel }}</span>
    </div>
    <div class="card-body">
      <div v-if="problems.length == 0" class="empty-table-message">
        {{ emptyTableLabel }}
      </div>
      <div v-else>
        <table class="table table-striped">
          <thead>
            <tr>
              <th class="text-center">{{ T.contestAddproblemProblemOrder }}</th>
              <th class="text-center">{{ problemTableHeaderLabel }}</th>
              <th class="text-center">{{ pointsTableHeaderLabel }}</th>
              <th class="text-center">{{ T.courseExtraPointsProblem }}</th>
              <th class="text-center">
                {{ T.wordsActions }}
              </th>
            </tr>
          </thead>
          <tbody v-sortable="{ onUpdate: sort }">
            <tr v-for="problem in problems" :key="problem.letter">
              <td class="text-center">
                <button
                  class="btn btn-link"
                  type="button"
                  :title="reorderButtonLabel"
                >
                  <font-awesome-icon icon="arrows-alt" />
                </button>
              </td>
              <td class="align-middle text-center">
                <a :href="`/arena/problem/${problem.alias}/`">{{
                  problem.alias
                }}</a>
              </td>
              <td class="align-middle">{{ problem.points }}</td>
              <td class="align-middle text-center">
                {{ problem.is_extra_problem ? T.wordsYes : T.wordsNo }}
              </td>
              <td class="button-column text-center">
                <button
                  class="btn btn-link"
                  :title="T.problemEditFormUpdateProblem"
                  data-edit-problem-version
                  @click.prevent="onEditProblem(problem)"
                >
                  <font-awesome-icon icon="edit" />
                </button>
                <button
                  class="btn btn-link"
                  :title="removeButtonLabel"
                  @click.prevent="onRemoveProblem(assignment, problem)"
                >
                  <font-awesome-icon icon="trash" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        <div>
          <button
            class="btn btn-primary"
            :disabled="!problemsOrderChanged"
            role="button"
            @click="saveNewOrder"
          >
            {{ T.wordsSaveNewOrder }}
          </button>
        </div>
      </div>
    </div>
    <div class="card-footer">
      <form @submit.prevent="">
        <div class="row">
          <div class="col-md-12">
            <div class="row">
              <div class="form-group col-md-5">
                <span class="faux-label">{{ problemCardFooterLabel }}</span>
                <omegaup-common-typeahead
                  :existing-options="searchResultProblems"
                  :activation-threshold="1"
                  :value.sync="problemAlias"
                  @update-existing-options="
                    (query) => $emit('update-search-result-problems', query)
                  "
                >
                </omegaup-common-typeahead>
                <small class="form-text text-muted">
                  {{ addCardFooterDescLabel }}
                </small>
              </div>
              <div class="form-group col-md-2">
                <span class="faux-label">{{ T.wordsPoints }}</span>
                <input v-model="points" type="number" class="form-control" />
              </div>
              <div class="form-group col-md-5">
                <span class="faux-label"
                  >{{ T.courseExtraPointsProblemLabel }}
                  <font-awesome-icon
                    :title="T.courseExtraPointsProblemDesc"
                    icon="info-circle"
                  />
                </span>
                <div class="form-control">
                  <label class="radio-inline"
                    ><input
                      v-model="isExtraProblem"
                      type="radio"
                      :value="true"
                    />{{ T.wordsYes }}</label
                  >
                  <label class="radio-inline ml-3"
                    ><input
                      v-model="isExtraProblem"
                      type="radio"
                      :value="false"
                    />{{ T.wordsNo }}</label
                  >
                </div>
              </div>
              <div class="form-group col-md-5">
                <span class="faux-label">{{
                  T.contestAddproblemChooseVersion
                }}</span>
                <div class="form-control form-group">
                  <div class="form-check form-check-inline">
                    <label class="form-check-label">
                      <input
                        v-model="useLatestVersion"
                        class="form-check-input"
                        data-use-latest-version-true
                        name="use-latest-version"
                        type="radio"
                        :value="true"
                      />{{ T.contestAddproblemLatestVersion }}
                    </label>
                  </div>
                  <div class="form-check form-check-inline">
                    <label class="form-check-label">
                      <input
                        v-model="useLatestVersion"
                        class="form-check-input"
                        data-use-latest-version-false
                        name="use-latest-version"
                        type="radio"
                        :value="false"
                      />{{ T.contestAddproblemOtherVersion }}
                    </label>
                  </div>
                </div>
              </div>
              <omegaup-problem-versions
                v-if="!useLatestVersion"
                v-model="selectedRevision"
                :log="versionLog"
                :published-revision="publishedRevision"
                :show-footer="false"
                @runs-diff="onRunsDiff"
              ></omegaup-problem-versions>
            </div>
            <div class="form-group text-right">
              <button
                data-add-problem
                class="btn btn-primary mr-2"
                type="submit"
                :disabled="!problemAlias"
                @click.prevent="
                  onSaveProblem(assignment, {
                    alias: problemAlias.key,
                    points: points,
                    commit: selectedRevision.commit,
                    is_extra_problem: isExtraProblem,
                  })
                "
              >
                {{ addProblemButtonLabel }}
              </button>
              <button
                class="btn btn-secondary"
                type="reset"
                @click.prevent="reset"
              >
                {{ T.wordsCancel }}
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
    <!-- card-body -->
  </div>
  <!-- card -->
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import { types } from '../../api_types';
import T from '../../lang';
import common_Typeahead from '../common/Typeahead.vue';
import problem_Versions from '../problem/Versions.vue';

import {
  FontAwesomeIcon,
  FontAwesomeLayers,
  FontAwesomeLayersText,
} from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
library.add(fas);

@Component({
  components: {
    'omegaup-common-typeahead': common_Typeahead,
    'omegaup-problem-versions': problem_Versions,
    'font-awesome-icon': FontAwesomeIcon,
    'font-awesome-layers': FontAwesomeLayers,
    'font-awesome-layers-text': FontAwesomeLayersText,
  },
})
export default class CourseProblemList extends Vue {
  @Prop() assignments!: types.CourseAssignment[];
  @Prop() assignmentProblems!: types.ProblemsetProblem[];
  @Prop() taggedProblems!: omegaup.Problem[];
  @Prop() selectedAssignment!: types.CourseAssignment;
  @Prop() searchResultProblems!: types.ListItem[];

  T = T;
  assignment: Partial<types.CourseAssignment> = this.selectedAssignment;
  problems: types.AddedProblem[] = this.assignmentProblems;
  difficulty = 'intro';
  topics: string[] = [];
  taggedProblemAlias = '';
  problemAlias: null | types.ListItem = null;
  points = 100;
  showTopicsAndDifficulty = false;
  problemsOrderChanged = false;
  useLatestVersion = true;
  isExtraProblem = false;
  versionLog: types.ProblemVersion[] = [];
  publishedRevision: null | types.ProblemVersion = null;
  selectedRevision: null | types.ProblemVersion = null;

  get tags(): string[] {
    let t = this.topics.slice();
    t.push(this.difficulty);
    return t;
  }

  get addCardHeaderTitleLabel(): string {
    return this.assignment.assignment_type === 'lesson'
      ? T.courseAddLecturesAdd
      : T.courseAddProblemsAdd;
  }

  get addCardHeaderDescLabel(): string {
    return this.assignment.assignment_type === 'lesson'
      ? T.courseAddLecturesEditAssignmentDesc
      : T.courseAddProblemsEditAssignmentDesc;
  }

  get emptyTableLabel(): string {
    return this.assignment.assignment_type === 'lesson'
      ? T.courseAssignmentLecturesEmpty
      : T.courseAssignmentProblemsEmpty;
  }

  get problemTableHeaderLabel(): string {
    return this.assignment.assignment_type === 'lesson'
      ? T.contestAddlectureLectureName
      : T.contestAddproblemProblemName;
  }

  get pointsTableHeaderLabel(): string {
    return this.assignment.assignment_type === 'lesson'
      ? T.contestAddlectureLecturePoints
      : T.contestAddproblemProblemPoints;
  }

  get reorderButtonLabel(): string {
    return this.assignment.assignment_type === 'lesson'
      ? T.courseAssignmentLectureReorder
      : T.courseAssignmentProblemReorder;
  }

  get removeButtonLabel(): string {
    return this.assignment.assignment_type === 'lesson'
      ? T.courseAssignmentLectureRemove
      : T.courseAssignmentProblemRemove;
  }

  get problemCardFooterLabel(): string {
    return this.assignment.assignment_type === 'lesson'
      ? T.wordsLecture
      : T.wordsProblem;
  }

  get addCardFooterDescLabel(): string {
    return this.assignment.assignment_type === 'lesson'
      ? T.courseAddLecturesAssignmentsDesc
      : T.courseAddProblemsAssignmentsDesc;
  }

  get addProblemButtonDisabled(): boolean {
    if (this.useLatestVersion) return !!this.problemAlias;
    return !this.selectedRevision;
  }

  get addProblemButtonLabel(): string {
    for (const problem of this.problems) {
      if (this.problemAlias?.key === problem.alias) {
        if (this.assignment.assignment_type === 'lesson') {
          return T.wordsUpdateLecture;
        }
        return T.wordsUpdateProblem;
      }
    }
    if (this.assignment.assignment_type === 'lesson') {
      return T.wordsAddLecture;
    }
    return T.wordsAddProblem;
  }

  sort(event: any) {
    this.problems.splice(
      event.newIndex,
      0,
      this.problems.splice(event.oldIndex, 1)[0],
    );
    this.problemsOrderChanged = true;
  }

  saveNewOrder() {
    this.$emit(
      'emit-sort',
      this.assignment.alias,
      this.assignmentProblems.map((problem) => problem.alias),
    );
    this.problemsOrderChanged = false;
  }

  onSaveProblem(
    assignment: types.CourseAssignment,
    problem: types.AddedProblem,
  ): void {
    this.$emit('save-problem', assignment, problem);
  }

  onEditProblem(problem: types.AddedProblem): void {
    this.problemAlias = { key: problem.alias, value: problem.alias };
  }

  onRemoveProblem(
    assignment: types.CourseAssignment,
    problem: types.AddedProblem,
  ): void {
    this.$emit('emit-remove-problem', assignment, problem);
  }

  onRunsDiff(
    versions: types.ProblemVersion[],
    selectedCommit: types.ProblemVersion,
  ): void {
    let found = false;
    for (const problem of this.problems) {
      if (this.problemAlias?.key === problem.alias) {
        found = true;
        break;
      }
    }
    if (!found) {
      return;
    }
    this.$emit('runs-diff', this, versions, selectedCommit);
  }

  @Watch('problemAlias')
  onAliasChange(newProblemAlias: null | types.ListItem) {
    if (!newProblemAlias) {
      this.versionLog = [];
      this.selectedRevision = this.publishedRevision;
      return;
    }
    this.$emit('change-alias', {
      target: this,
      request: { problemAlias: newProblemAlias.key },
    });
  }

  @Watch('publishedRevision')
  onPublishedRevisionChange(newPublishedRevision: types.ProblemVersion) {
    if (!newPublishedRevision) {
      return;
    }
    this.useLatestVersion =
      newPublishedRevision.commit === this.versionLog[0].commit;
  }

  @Watch('useLatestVersion')
  onUseLatestVersionChange(newUseLatestVersion: boolean) {
    if (!newUseLatestVersion) {
      return;
    }
    this.selectedRevision = this.versionLog[0];
  }

  @Watch('assignmentProblems')
  onAssignmentProblemChange(newValue: types.AddedProblem[]): void {
    this.problems = newValue;
  }

  @Watch('problems')
  onProblemsChange(): void {
    this.reset();
  }

  @Watch('assignment')
  onAssignmentChange(newVal: types.CourseAssignment): void {
    this.$emit('emit-select-assignment', newVal);
  }

  @Watch('selectedAssignment')
  onSelectedAssignmentChange(newVal: types.CourseAssignment): void {
    this.assignment = newVal;
  }

  @Watch('taggedProblemAlias')
  onTaggedProblemAliasChange(newValue: string) {
    this.problemAlias = { key: newValue, value: newValue };
  }

  @Watch('tags')
  onTagsChange() {
    this.$emit('emit-tags', this.tags);
  }

  reset(): void {
    this.problemAlias = null;
    this.points = 100;
    this.useLatestVersion = true;
  }
}
</script>
