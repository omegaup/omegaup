<template>
  <div class="card" data-course-problemlist>
    <div class="card-header">
      <h5>
        {{ T.courseAddProblemsAdd }}
      </h5>
      <span>{{ T.courseAddProblemsEditAssignmentDesc }}</span>
    </div>
    <div class="card-body">
      <div class="empty-table-message" v-if="problems.length == 0">
        {{ T.courseAssignmentProblemsEmpty }}
      </div>
      <div v-else>
        <table class="table table-striped">
          <thead>
            <tr>
              <th>{{ T.contestAddproblemProblemOrder }}</th>
              <th>{{ T.contestAddproblemProblemName }}</th>
              <th>{{ T.contestAddproblemProblemPoints }}</th>
              <th>{{ T.contestAddproblemProblemRemove }}</th>
            </tr>
          </thead>
          <tbody v-sortable="{ onUpdate: sort }">
            <tr v-bind:key="problem.letter" v-for="problem in problems">
              <td>
                <button
                  class="btn btn-link"
                  type="button"
                  v-bind:title="T.courseAssignmentProblemReorder"
                >
                  <font-awesome-icon icon="arrows-alt" />
                </button>
              </td>
              <td class="align-middle">{{ problem.alias }}</td>
              <td class="align-middle">{{ problem.points }}</td>
              <td class="button-column">
                <button
                  class="btn btn-link"
                  v-bind:title="T.courseAssignmentProblemRemove"
                  v-on:click.prevent="onRemoveProblem(assignment, problem)"
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
            v-bind:disabled="!problemsOrderChanged"
            role="button"
            v-on:click="saveNewOrder"
          >
            {{ T.wordsSaveNewOrder }}
          </button>
        </div>
      </div>
    </div>
    <div class="card-footer">
      <form v-on:submit.prevent="">
        <div class="row">
          <div class="col-md-12">
            <div class="row">
              <div class="form-group col-md-5">
                <label
                  >{{ T.wordsProblem }}
                  <omegaup-autocomplete
                    class="form-control"
                    v-bind:init="(el) => typeahead.problemTypeahead(el)"
                    v-model="problemAlias"
                  ></omegaup-autocomplete
                ></label>
                <p class="help-block">
                  {{ T.courseAddProblemsAssignmentsDesc }}
                </p>
              </div>
              <div class="form-group col-md-2">
                <label
                  >{{ T.wordsPoints }}
                  <input type="number" class="form-control" v-model="points" />
                </label>
              </div>
              <div class="form-group col-md-5">
                <label for="use-latest-version"
                  >{{ T.contestAddproblemChooseVersion }}
                  <div class="form-control form-group">
                    <div class="form-check form-check-inline">
                      <label class="form-check-label">
                        <input
                          class="form-check-input"
                          type="radio"
                          v-model="useLatestVersion"
                          v-bind:value="true"
                        />{{ T.contestAddproblemLatestVersion }}
                      </label>
                    </div>
                    <div class="form-check form-check-inline">
                      <label class="form-check-label">
                        <input
                          class="form-check-input"
                          type="radio"
                          v-model="useLatestVersion"
                          v-bind:value="false"
                        />{{ T.contestAddproblemOtherVersion }}
                      </label>
                    </div>
                  </div>
                </label>
              </div>
              <omegaup-problem-versions
                v-if="!useLatestVersion"
                v-bind:log="versionLog"
                v-bind:published-revision="publishedRevision"
                v-bind:show-footer="false"
                v-model="selectedRevision"
                v-on:runs-diff="onRunsDiff"
              ></omegaup-problem-versions>
            </div>
            <div class="form-group text-right">
              <button
                data-add-problem
                class="btn btn-primary mr-2"
                type="submit"
                v-bind:disabled="problemAlias.length == 0"
                v-on:click.prevent="
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
                v-on:click.prevent="reset"
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

<style lang="scss" scoped>
.form-group > label {
  width: 100%;
}
</style>

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

const emptyCommit: types.ProblemVersion = {
  author: {},
  commit: '',
  committer: {},
  message: '',
  parents: [],
  tree: {},
  version: '',
};

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
  publishedRevision = emptyCommit;
  selectedRevision = emptyCommit;

  get tags(): string[] {
    let t = this.topics.slice();
    t.push(this.difficulty);
    return t;
  }

  get addProblemButtonDisabled(): boolean {
    if (this.useLatestVersion) {
      return this.problemAlias === '';
    } else {
      return this.selectedRevision.commit === '';
    }
  }

  get addProblemButtonLabel(): string {
    for (const problem of this.problems) {
      if (this.problemAlias === problem.alias) {
        return T.wordsUpdateProblem;
      }
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
      this.selectedRevision = this.publishedRevision = emptyCommit;
      return;
    }
    this.$emit('change-alias', this, newProblemAlias);
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
