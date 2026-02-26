<template>
  <div class="w-100 mt-2">
    <button
      v-if="previousAssignment !== null"
      class="btn btn-primary col-md-12 col-sm-12 col-xs-12 mb-2"
      :title="previousAssignment.name"
      role="button"
      @click="$emit('navigate-to-assignment', previousAssignment.alias)"
    >
      <font-awesome-icon :icon="['fas', 'chevron-circle-left']" />
      {{ previousAssignment.name }}
    </button>
    <button
      v-if="nextAssignment !== null"
      class="btn btn-primary col-md-12 col-sm-12 col-xs-12"
      :title="nextAssignment.name"
      role="button"
      @click="$emit('navigate-to-assignment', nextAssignment.alias)"
    >
      {{ nextAssignment.name }}
      <font-awesome-icon :icon="['fas', 'chevron-circle-right']" />
    </button>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
  faChevronCircleLeft,
  faChevronCircleRight,
} from '@fortawesome/free-solid-svg-icons';
library.add(faChevronCircleLeft);
library.add(faChevronCircleRight);

@Component({
  components: {
    FontAwesomeIcon,
  },
})
export default class ArenaNavbarAssignments extends Vue {
  @Prop() assignments!: omegaup.Assignment[];
  @Prop() currentAssignment!: omegaup.Assignment;

  T = T;

  get previousAssignment(): omegaup.Assignment | null {
    if (this.currentAssignmentIndex === 0) {
      return null;
    }
    return this.assignments[this.currentAssignmentIndex - 1];
  }

  get nextAssignment(): omegaup.Assignment | null {
    if (this.currentAssignmentIndex === this.assignments.length - 1) {
      return null;
    }
    return this.assignments[this.currentAssignmentIndex + 1];
  }

  private get currentAssignmentIndex(): number {
    // Getting index of current assignment
    return this.assignments.findIndex(
      (assignment) => assignment.alias === this.currentAssignment.alias,
    );
  }
}
</script>

<style>
.navbar-assignments > button {
  margin-bottom: 0.5em;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
