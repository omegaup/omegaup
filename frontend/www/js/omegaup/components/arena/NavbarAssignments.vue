<template>
  <div class="navbar-assignments">
    <div class="text-left col-md-6 col-sm-6 col-xs-6">
      <a
        class="btn btn-primary btn-sm prev"
        title=""
        v-on:click="$emit('navigate-to-assignment', previous)"
        v-bind:class="{ disabled: previous === null }"
        role="button"
        >{{ T.wordsPrevAssignment }}</a
      >
    </div>
    <div class="text-right col-md-6 col-sm-6 col-xs-6">
      <a
        class="btn btn-primary btn-sm next"
        title=""
        v-on:click="$emit('navigate-to-assignment', next)"
        v-bind:class="{ disabled: next === null }"
        role="button"
        >{{ T.wordsNextAssignment }}</a
      >
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
  @Prop() next!: omegaup.Assignment;
  @Prop() previous!: omegaup.Assignment;

  T = T;
}
</script>
