<template>
  <div class="card" data-course-problemlist>
    <div class="card-header">
      <h5 v-if="assignment.assignment_type == 'lesson'">
        {{ T.courseAddLecturesAdd }}
      </h5>
      <h5 v-else>
        {{ T.courseAddProblemsAdd }}
      </h5>
      <span v-if="assignment.assignment_type == 'lesson'">{{
        T.courseAddLecturesEditAssignmentDesc
      }}</span>
      <span v-else>{{ T.courseAddProblemsEditAssignmentDesc }}</span>
    </div>
    <div class="card-body">
      <div
        v-if="problems.length == 0 && assignment.assignment_type == 'lesson'"
        class="empty-table-message"
      >
        {{ T.courseAssignmentLecturesEmpty }}
      </div>
      <div v-else-if="problems.length == 0" class="empty-table-message">
        {{ T.courseAssignmentProblemsEmpty }}
      </div>
      <div v-else>
        <table class="table table-striped">
          <thead>
            <tr>
              <th>{{ T.contestAddproblemProblemOrder }}</th>
              <th v-if="assignment.assignment_type == 'lesson'">
                {{ T.contestAddlectureLectureName }}
              </th>
              <th v-else>{{ T.contestAddproblemProblemName }}</th>
              <th v-if="assignment.assignment_type == 'lesson'">
                {{ T.contestAddlectureLecturePoints }}
              </th>
              <th v-else>{{ T.contestAddproblemProblemPoints }}</th>
              <th>{{ T.contestAddproblemProblemRemove }}</th>
            </tr>
          </thead>
          <tbody v-sortable="{ onUpdate: sort }">
            <tr v-for="problem in problems" :key="problem.letter">
              <td>
                <button
                  v-if="assignment.assignment_type == 'lesson'"
                  class="btn btn-link"
                  type="button"
                  :title="T.courseAssignmentLectureReorder"
                >
                  <font-awesome-icon icon="arrows-alt" />
                </button>
                <button
                  v-else
                  class="btn btn-link"
                  type="button"
                  :title="T.courseAssignmentProblemReorder"
                >
                  <font-awesome-icon icon="arrows-alt" />
                </button>
              </td>
              <td class="align-middle">
                <a :href="`/arena/problem/${problem.alias}/`">{{
                  problem.alias
                }}</a>
              </td>
              <td class="align-middle">{{ problem.points }}</td>
              <td class="button-column">
                <button
                  v-if="assignment.assignment_type == 'lesson'"
                  class="btn btn-link"
                  :title="T.courseAssignmentLectureRemove"
                  @click.prevent="onRemoveProblem(assignment, problem)"
                >
                  <font-awesome-icon icon="trash" />
                </button>
                <button
                  v-else
                  class="btn btn-link"
                  :title="T.courseAssignmentProblemRemove"
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
                <label
                  v-if="assignment.assignment_type == 'lesson'"
                  class="w-100"
                  >{{ T.wordsLecture }}
                  <omegaup-autocomplete
                    v-model="problemAlias"
                    class="form-control"
                    :init="(el) => typeahead.problemTypeahead(el)"
                  ></omegaup-autocomplete
                ></label>
                <label v-else class="w-100"
                  >{{ T.wordsProblem }}
                  <omegaup-autocomplete
                    v-model="problemAlias"
                    class="form-control"
                    :init="(el) => typeahead.problemTypeahead(el)"
                  ></omegaup-autocomplete
                ></label>
                <p
                  v-if="assignment.assignment_type == 'lesson'"
                  class="help-block"
                >
                  {{ T.courseAddLecturesAssignmentsDesc }}
                </p>
                <p v-else class="help-block">
                  {{ T.courseAddProblemsAssignmentsDesc }}
                </p>
              </div>
              <div class="form-group col-md-2">
                <label class="w-100"
                  >{{ T.wordsPoints }}
                  <input v-model="points" type="number" class="form-control" />
                </label>
              </div>
              <div class="form-group col-md-5">
                <label for="use-latest-version" class="w-100"
                  >{{ T.contestAddproblemChooseVersion }}
                  <div class="form-control form-group">
                    <div class="form-check form-check-inline">
                      <label class="form-check-label">
                        <input
                          v-model="useLatestVersion"
                          class="form-check-input"
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
                          type="radio"
                          :value="false"
                        />{{ T.contestAddproblemOtherVersion }}
                      </label>
                    </div>
                  </div>
                </label>
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
                :disabled="problemAlias.length == 0"
                @click.prevent="
                  onSaveProblem(assignment, {
                    alias: problemAlias,
                    points: points,
                    commit: selectedRevision.commit,
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
import * as typeahead from '../../typeahead';
import Autocomplete from '../Autocomplete.vue';
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
    'omegaup-autocomplete': Autocomplete,
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

  typeahead = typeahead;
  T = T;
  assignment: Partial<types.CourseAssignment> = this.selectedAssignment;
  problems: types.AddedProblem[] = this.assignmentProblems;
  difficulty = 'intro';
  topics: string[] = [];
  taggedProblemAlias = '';
  problemAlias = '';
  points = 100;
  showTopicsAndDifficulty = false;
  problemsOrderChanged = false;
  useLatestVersion = true;
  versionLog: types.ProblemVersion[] = [];
  publishedRevision: null | types.ProblemVersion = null;
  selectedRevision: null | types.ProblemVersion = null;

  get tags(): string[] {
    let t = this.topics.slice();
    t.push(this.difficulty);
    return t;
  }

  get addProblemButtonDisabled(): boolean {
    if (this.useLatestVersion) return this.problemAlias === '';
    return !this.selectedRevision;
  }

  get addProblemButtonLabel(): string {
    for (const problem of this.problems) {
      if (this.problemAlias === problem.alias) {
        if (this.assignment.assignment_type == 'lesson') {
          return T.wordsUpdateLecture;
        }
        return T.wordsUpdateProblem;
      }
    }
    if (this.assignment.assignment_type == 'lesson') {
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
      if (this.problemAlias === problem.alias) {
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
  onAliasChange(newProblemAlias: string) {
    if (!newProblemAlias) {
      this.versionLog = [];
      this.selectedRevision = this.publishedRevision;
      return;
    }
    this.$emit('change-alias', {
      target: this,
      request: { problemAlias: newProblemAlias },
    });
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
  onTaggedProblemAliasChange() {
    this.problemAlias = this.taggedProblemAlias;
  }

  @Watch('tags')
  onTagsChange() {
    this.$emit('emit-tags', this.tags);
  }

  reset(): void {
    this.problemAlias = '';
    this.points = 100;
    this.useLatestVersion = true;
  }
}
</script>
