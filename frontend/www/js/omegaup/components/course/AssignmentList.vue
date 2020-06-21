<template>
  <div class="omegaup-course-assignmentlist panel">
    <div class="panel-heading">
      <h3>{{ T.wordsAssignments }}</h3>
    </div>
    <div class="panel-body" v-if="assignments.length == 0">
      <div class="empty-category">
        {{ T.courseAssignmentEmpty }}
      </div>
    </div>
    <div class="panel-body" v-else="">
      <table class="table table-striped">
        <thead>
          <tr>
            <th colspan="4" v-text="T.wordsHomeworks"></th>
          </tr>
        </thead>
        <tbody v-sortable="{ onUpdate: sortHomeworks }">
          <tr v-bind:key="assignment.alias" v-for="assignment in homeworks">
            <td>
              <a v-bind:title="T.courseAssignmentReorder"
                ><span
                  aria-hidden="true"
                  class="glyphicon glyphicon-move handle"
                ></span
              ></a>
            </td>
            <td>
              <a v-bind:href="assignmentUrl(assignment)">{{
                assignment.name
              }}</a>
            </td>
            <td class="button-column">
              <a
                v-bind:title="T.courseAssignmentEdit"
                v-on:click="$emit('edit', assignment)"
                ><span
                  aria-hidden="true"
                  class="glyphicon glyphicon-edit"
                ></span
              ></a>
            </td>
            <td class="button-column">
              <a
                v-bind:title="T.courseAddProblemsAdd"
                v-on:click="$emit('add-problems', assignment)"
                ><span
                  aria-hidden="true"
                  class="glyphicon glyphicon-th-list"
                ></span
              ></a>
            </td>
            <td class="button-column">
              <span
                v-bind:title="T.assignmentRemoveAlreadyHasRuns"
                aria-hidden="true"
                class="glyphicon glyphicon-remove disabled"
                v-if="assignment.has_runs"
              ></span>
              <a
                href="#"
                v-else=""
                v-bind:title="T.courseAssignmentDelete"
                v-on:click="$emit('delete', assignment)"
                ><span
                  aria-hidden="true"
                  class="glyphicon glyphicon-remove"
                ></span
              ></a>
            </td>
          </tr>
        </tbody>
      </table>
      <div>
        <a
          class="btn btn-primary"
          v-bind:class="{ disabled: !homeworksOrderChanged }"
          role="button"
          v-on:click="saveNewOrder('homeworks')"
        >
          {{ T.wordsSaveNewOrder }}
        </a>
      </div>
      <hr />
      <table class="table table-striped">
        <thead>
          <tr>
            <th colspan="4" v-text="T.wordsExams"></th>
          </tr>
        </thead>
        <tbody v-sortable="{ onUpdate: sortTests }">
          <tr v-bind:key="assignment.alias" v-for="assignment in tests">
            <td>
              <a v-bind:title="T.courseAssignmentReorder"
                ><span
                  aria-hidden="true"
                  class="glyphicon glyphicon-move handle"
                ></span
              ></a>
            </td>
            <td>
              <a v-bind:href="assignmentUrl(assignment)">{{
                assignment.name
              }}</a>
            </td>
            <td class="button-column">
              <a
                v-bind:title="T.courseAssignmentEdit"
                v-on:click="$emit('edit', assignment)"
                ><span
                  aria-hidden="true"
                  class="glyphicon glyphicon-edit"
                ></span
              ></a>
            </td>
            <td class="button-column">
              <a
                v-bind:title="T.courseAssignmentDelete"
                v-on:click="$emit('delete', assignment)"
                ><span
                  aria-hidden="true"
                  class="glyphicon glyphicon-remove"
                ></span
              ></a>
            </td>
          </tr>
        </tbody>
      </table>
      <div>
        <a
          class="btn btn-primary"
          v-bind:class="{ disabled: !testsOrderChanged }"
          role="button"
          v-on:click="saveNewOrder('tests')"
        >
          {{ T.wordsSaveNewOrder }}
        </a>
      </div>
    </div>
    <div class="panel-footer">
      <form class="new">
        <div class="row">
          <div class="form-group col-md-12">
            <div class="pull-right">
              <button
                class="btn btn-primary"
                type="submit"
                v-on:click.prevent="$emit('new')"
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

@Component
export default class CourseAssignmentList extends Vue {
  @Prop() assignments!: omegaup.Assignment[];
  @Prop() courseAlias!: string;

  testsOrderChanged = false;
  homeworksOrderChanged = false;
  T = T;

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
      param = this.homeworks.map(homework => homework.alias);
      this.homeworksOrderChanged = false;
    } else {
      param = this.tests.map(test => test.alias);
      this.testsOrderChanged = false;
    }
    this.$emit(`sort-${type}`, this.courseAlias, param);
  }
}
</script>
