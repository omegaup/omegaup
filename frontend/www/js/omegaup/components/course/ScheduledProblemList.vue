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
              <th>{{ problemTableHeaderLabel }}</th>
              <th>{{ pointsTableHeaderLabel }}</th>
              <th>{{ T.contestAddproblemProblemRemove }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="problem in problems" :key="problem.alias">
              <td class="align-middle">
                <a :href="`/arena/problem/${problem.alias}/`">{{
                  problem.alias
                }}</a>
              </td>
              <td class="align-middle">{{ problem.points }}</td>
              <td class="button-column align-middle">
                <button
                  class="btn btn-link"
                  :title="removeButtonLabel"
                  @click.prevent="onRemoveProblem(problem)"
                >
                  <font-awesome-icon icon="trash" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer" data-course-add-problem>
      <form>
        <div class="row">
          <div class="col-md-12">
            <div class="row">
              <div class="form-group col-md-8">
                <label
                  >{{ problemCardFooterLabel }}
                  <omegaup-common-typeahead
                    :existing-options="searchResultProblems"
                    :activation-threshold="1"
                    :value.sync="problemAlias"
                    @update-existing-options="
                      (query) => $emit('update-search-result-problems', query)
                    "
                  ></omegaup-common-typeahead>
                </label>
                <p class="help-block">
                  {{ addCardFooterDescLabel }}
                </p>
              </div>
              <div class="form-group col-md-4">
                <label
                  >{{ T.wordsPoints }}
                  <input v-model="points" type="number" class="form-control" />
                </label>
              </div>
            </div>
            <div class="form-group text-right">
              <button
                data-add-problem
                class="btn btn-primary mr-2"
                type="submit"
                :disabled="!problemAlias"
                @click.prevent="
                  onAddProblem({ alias: problemAlias.key, points: points })
                "
              >
                {{ addButtonLabel }}
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
    'font-awesome-icon': FontAwesomeIcon,
    'font-awesome-layers': FontAwesomeLayers,
    'font-awesome-layers-text': FontAwesomeLayersText,
  },
})
export default class CourseScheduledProblemList extends Vue {
  @Prop() assignments!: types.CourseAssignment[];
  @Prop() assignmentProblems!: types.ProblemsetProblem[];
  @Prop() taggedProblems!: omegaup.Problem[];
  @Prop() selectedAssignment!: types.CourseAssignment;
  @Prop() searchResultProblems!: types.ListItem[];

  T = T;
  assignment: Partial<types.CourseAssignment> = this.selectedAssignment;
  problems: types.AddedProblem[] = this.assignmentProblems;
  taggedProblemAlias = '';
  problemAlias: null | types.ListItem = null;
  points = 100;
  showTopicsAndDifficulty = false;

  get addCardHeaderTitleLabel(): string {
    return this.assignment.assignment_type === 'lesson'
      ? T.courseAddLecturesAdd
      : T.courseAddProblemsAdd;
  }

  get addCardHeaderDescLabel(): string {
    return this.assignment.assignment_type === 'lesson'
      ? T.courseAddLecturesAddAssignmentDesc
      : T.courseAddProblemsAddAssignmentDesc;
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

  get addButtonLabel(): string {
    return this.assignment.assignment_type === 'lesson'
      ? T.courseEditAddLectures
      : T.courseEditAddProblems;
  }

  onAddProblem(problem: types.AddedProblem): void {
    const problemAlias = { key: problem.alias, value: problem.alias };
    const currentProblem = this.problems.find(
      (problem) => problem.alias === problemAlias.key,
    );
    if (!currentProblem) {
      this.problems.push(problem);
      return;
    }
    currentProblem.points = problem.points;
  }

  onRemoveProblem(problem: types.AddedProblem): void {
    const problemAlias = { key: problem.alias, value: problem.alias };
    this.problems = this.problems.filter(
      (problem) => problem.alias !== problemAlias.key,
    );
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

  reset(): void {
    this.problemAlias = null;
    this.points = 100;
  }
}
</script>

<style lang="scss" scoped>
.form-group > label {
  width: 100%;
}
</style>
