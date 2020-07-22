<template>
  <div class="omegaup-course-assignmentlist card">
    <div class="card-header">
      <h3>{{ T.wordsAssignments }}</h3>
    </div>
    <div class="card-body">
      <div class="card-body" v-if="homeworks.length === 0">
        <div class="empty-category">
          {{ T.courseAssignmentEmpty }}
        </div>
      </div>
      <table class="table table-striped" v-else="">
        <thead>
          <tr>
            <th colspan="5">{{ T.wordsHomeworks }}</th>
          </tr>
        </thead>
        <tbody v-sortable="{ onUpdate: sortHomeworks }">
          <tr v-bind:key="assignment.alias" v-for="assignment in homeworks">
            <td>
              <button
                class="btn btn-link"
                v-bind:title="T.courseAssignmentReorder"
              >
                <font-awesome-icon icon="arrows-alt" />
              </button>
            </td>
            <td>
              <a v-bind:href="assignmentUrl(assignment)">{{
                assignment.name
              }}</a>
            </td>
            <td class="button-column">
              <button
                class="btn btn-link"
                v-bind:title="T.courseAssignmentEdit"
                v-on:click="$emit('emit-edit', assignment)"
              >
                <font-awesome-icon icon="edit" />
              </button>
            </td>
            <td class="button-column">
              <button
                class="btn btn-link"
                v-bind:title="T.courseAddProblemsAdd"
                v-on:click="$emit('emit-add-problems', assignment)"
              >
                <font-awesome-icon icon="list-alt" />
              </button>
            </td>
            <td class="button-column">
              <font-awesome-icon
                v-bind:title="T.assignmentRemoveAlreadyHasRuns"
                icon="trash"
                v-if="assignment.has_runs"
                class="disabled"
              />
              <button
                class="btn btn-link"
                v-else=""
                v-bind:title="T.courseAssignmentDelete"
                v-on:click="$emit('emit-delete', assignment)"
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
          v-if="homeworks.length > 1"
          v-bind:class="{ disabled: !homeworksOrderChanged }"
          role="button"
          v-on:click="saveNewOrder('homeworks')"
        >
          {{ T.wordsSaveNewOrder }}
        </button>
      </div>
      <hr />
      <div class="card-body" v-if="tests.length === 0">
        <div class="empty-category">
          {{ T.courseExamEmpty }}
        </div>
      </div>
      <table class="table table-striped" v-else="">
        <thead>
          <tr>
            <th colspan="5">{{ T.wordsExams }}</th>
          </tr>
        </thead>
        <tbody v-sortable="{ onUpdate: sortTests }">
          <tr v-bind:key="assignment.alias" v-for="assignment in tests">
            <td>
              <button
                class="btn btn-link"
                v-bind:title="T.courseAssignmentReorder"
              >
                <font-awesome-icon icon="arrows-alt" />
              </button>
            </td>
            <td>
              <a v-bind:href="assignmentUrl(assignment)">{{
                assignment.name
              }}</a>
            </td>
            <td class="button-column">
              <button
                class="btn btn-link"
                v-bind:title="T.courseAssignmentEdit"
                v-on:click="$emit('emit-edit', assignment)"
              >
                <font-awesome-icon icon="edit" />
              </button>
            </td>
            <td class="button-column">
              <button
                class="btn btn-link"
                v-bind:title="T.courseAddProblemsAdd"
                v-on:click="$emit('emit-add-problems', assignment)"
              >
                <font-awesome-icon icon="list-alt" />
              </button>
            </td>
            <td class="button-column">
              <font-awesome-icon
                v-bind:title="T.assignmentRemoveAlreadyHasRuns"
                icon="trash"
                v-if="assignment.has_runs"
                class="disabled"
              />
              <button
                class="btn btn-link"
                v-bind:title="T.courseAssignmentDelete"
                v-on:click="$emit('emit-delete', assignment)"
                v-else=""
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
          v-if="tests.length > 1"
          v-bind:class="{ disabled: !testsOrderChanged }"
          role="button"
          v-on:click="saveNewOrder('tests')"
        >
          {{ T.wordsSaveNewOrder }}
        </button>
      </div>
    </div>
    <div class="card-footer">
      <form class="new">
        <div class="row">
          <div class="form-group col-md-12">
            <div class="text-right">
              <button
                v-if="assignmentFormMode === AssignmentFormMode.Default"
                class="btn btn-primary"
                type="submit"
                v-on:click.prevent="$emit('emit-new')"
              >
                {{ T.courseAssignmentNew }}
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<style>
.disabled {
  color: lightgrey;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';

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
    'font-awesome-icon': FontAwesomeIcon,
    'font-awesome-layers': FontAwesomeLayers,
    'font-awesome-layers-text': FontAwesomeLayersText,
  },
})
export default class CourseAssignmentList extends Vue {
  @Prop() assignments!: omegaup.Assignment[];
  @Prop() courseAlias!: string;
  @Prop() assignmentFormMode!: omegaup.AssignmentFormMode;

  testsOrderChanged = false;
  homeworksOrderChanged = false;
  T = T;
  AssignmentFormMode = omegaup.AssignmentFormMode;

  get homeworks(): omegaup.Assignment[] {
    return this.assignments.filter((assignment: omegaup.Assignment) => {
      return assignment.assignment_type == 'homework';
    });
  }

  get tests(): omegaup.Assignment[] {
    return this.assignments.filter((assignment: omegaup.Assignment) => {
      return assignment.assignment_type == 'test';
    });
  }

  assignmentUrl(assignment: omegaup.Assignment): string {
    return `/course/${this.courseAlias}/assignment/${assignment.alias}/`;
  }

  sortHomeworks(event: any): void {
    this.homeworks.splice(
      event.newIndex,
      0,
      this.homeworks.splice(event.oldIndex, 1)[0],
    );
    this.homeworksOrderChanged = true;
  }

  sortTests(event: any): void {
    this.tests.splice(
      event.newIndex,
      0,
      this.tests.splice(event.oldIndex, 1)[0],
    );
    this.testsOrderChanged = true;
  }

  saveNewOrder(type: string): void {
    let param: string[] = [];
    if (type === 'homeworks') {
      param = this.homeworks.map((homework) => homework.alias);
      this.homeworksOrderChanged = false;
    } else {
      param = this.tests.map((test) => test.alias);
      this.testsOrderChanged = false;
    }
    this.$emit(`emit-sort-${type}`, this.courseAlias, param);
  }
}
</script>
