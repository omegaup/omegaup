<template>
  <div class="card" data-course-problemlist>
    <div class="card-header">
      <h5>
        {{ T.courseAddProblemsAdd }}
      </h5>
      <span>{{ T.courseAddProblemsAddAssignmentDesc }}</span>
    </div>
    <div class="card-body">
      <div v-if="problems.length == 0" class="empty-table-message">
        {{ T.courseAssignmentProblemsEmpty }}
      </div>
      <div v-else>
        <table class="table table-striped">
          <thead>
            <tr>
              <th>{{ T.contestAddproblemProblemName }}</th>
              <th>{{ T.contestAddproblemProblemPoints }}</th>
              <th>{{ T.contestAddproblemProblemRemove }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="problem in problems">
              <td class="align-middle">{{ problem.alias }}</td>
              <td class="align-middle">{{ problem.points }}</td>
              <td class="button-column align-middle">
                <button
                  class="btn btn-link"
                  :title="T.courseAssignmentProblemRemove"
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
    <div class="card-footer">
      <form>
        <div class="row">
          <div class="col-md-12">
            <div class="row">
              <div class="form-group col-md-8">
                <label
                  >{{ T.wordsProblem }}
                  <omegaup-autocomplete
                    v-model="problemAlias"
                    class="form-control"
                    :init="(el) => typeahead.problemTypeahead(el)"
                  ></omegaup-autocomplete
                ></label>
                <p class="help-block">
                  {{ T.courseAddProblemsAssignmentsDesc }}
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
                :disabled="problemAlias.length == 0"
                @click.prevent="
                  onAddProblem({ alias: problemAlias, points: points })
                "
              >
                {{ T.courseEditAddProblems }}
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

  typeahead = typeahead;
  T = T;
  assignment: Partial<types.CourseAssignment> = this.selectedAssignment;
  problems: types.AddedProblem[] = this.assignmentProblems;
  taggedProblemAlias = '';
  problemAlias = '';
  points = 100;
  showTopicsAndDifficulty = false;

  onAddProblem(problem: types.AddedProblem): void {
    const problemAlias = problem.alias;
    const currentProblem = this.problems.find(
      (problem) => problem.alias === problemAlias,
    );
    if (!currentProblem) {
      this.problems.push(problem);
      return;
    }
    currentProblem.points = problem.points;
  }

  onRemoveProblem(problem: types.AddedProblem): void {
    const problemAlias = problem.alias;
    this.problems = this.problems.filter(
      (problem) => problem.alias !== problemAlias,
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
  onTaggedProblemAliasChange() {
    this.problemAlias = this.taggedProblemAlias;
  }

  reset(): void {
    this.problemAlias = '';
    this.points = 100;
  }
}
</script>
