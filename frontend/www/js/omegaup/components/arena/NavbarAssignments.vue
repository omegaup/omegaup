<template>
  <div class="navbar-assignments">
    <div class="text-left col-md-6 col-sm-6 col-xs-6">
      <a
        class="btn btn-primary btn-sm prev"
        title=""
        v-on:click="$emit('navigate-to-assignment', previousAssignmentAlias)"
        v-bind:class="{ disabled: previousAssignmentAlias === null }"
        role="button"
      >
        <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span
        >{{ T.wordsPrevAssignment }}</a
      >
    </div>
    <div class="text-right col-md-6 col-sm-6 col-xs-6">
      <a
        class="btn btn-primary btn-sm next"
        title=""
        v-on:click="$emit('navigate-to-assignment', nextAssignmentAlias)"
        v-bind:class="{ disabled: nextAssignmentAlias === null }"
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

  get previousAssignmentAlias(): string | null {
    // Getting index of current assignment
    const currentAssignmentIndex = this.assignments.findIndex(
      assignment => assignment.alias === this.currentAssignmentAlias,
    );

    if (currentAssignmentIndex === 0) {
      return null;
    }
    return this.assignments[currentAssignmentIndex - 1].alias;
  }

  get nextAssignmentAlias(): string | null {
    // Getting index of current assignment
    const currentAssignmentIndex = this.assignments.findIndex(
      assignment => assignment.alias === this.currentAssignmentAlias,
    );

    if (currentAssignmentIndex === this.assignments.length - 1) {
      return null;
    }
    return this.assignments[currentAssignmentIndex + 1].alias;
  }
}
</script>
