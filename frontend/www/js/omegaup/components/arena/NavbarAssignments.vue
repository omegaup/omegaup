<template>
  <div class="panel panel-default">
    <div class="panel-heading">{{ T.wordsAssignments }}</div>
    <div class="panel-body">
      <div class="text-left col-md-6 col-sm-6 col-xs-6">
        <a
          class="btn btn-primary btn-sm prev"
          title=""
          v-on:click="$emit('navigate-to-assignment', previousAssignment.alias)"
          v-bind:class="{ disabled: previousAssignment === null }"
          v-bind:title="
            previousAssignment !== null ? previousAssignment.name : ''
          "
          role="button"
        >
          <span
            class="glyphicon glyphicon-chevron-left"
            aria-hidden="true"
          ></span
          >{{ T.wordsPrevAssignment }}</a
        >
      </div>
      <div class="text-right col-md-6 col-sm-6 col-xs-6">
        <a
          class="btn btn-primary btn-sm next"
          title=""
          v-on:click="$emit('navigate-to-assignment', nextAssignment.alias)"
          v-bind:class="{ disabled: nextAssignment === null }"
          v-bind:title="nextAssignment !== null ? nextAssignment.name : ''"
          role="button"
        >
          {{ T.wordsNextAssignment
          }}<span
            class="glyphicon glyphicon-chevron-right"
            aria-hidden="true"
          ></span
        ></a>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import omegaup from '../../api.js';
import { T } from '../../omegaup.js';

@Component
export default class ArenaNavbarAssignments extends Vue {
  @Prop() assignments!: omegaup.Assignment[];
  @Prop() currentAssignmentAlias!: string;

  T = T;

  get previousAssignment(): omegaup.Assignment | null {
    // Getting index of current assignment
    const currentAssignmentIndex = this.assignments.findIndex(
      assignment => assignment.alias === this.currentAssignmentAlias,
    );

    if (currentAssignmentIndex === 0) {
      return null;
    }
    return this.assignments[currentAssignmentIndex - 1];
  }

  get nextAssignment(): omegaup.Assignment | null {
    // Getting index of current assignment
    const currentAssignmentIndex = this.assignments.findIndex(
      assignment => assignment.alias === this.currentAssignmentAlias,
    );

    if (currentAssignmentIndex === this.assignments.length - 1) {
      return null;
    }
    return this.assignments[currentAssignmentIndex + 1];
  }
}
</script>
