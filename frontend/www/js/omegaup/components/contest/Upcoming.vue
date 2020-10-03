<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title">{{ T.frontPageNextContests }}</h3>
    </div>
    <ul
      v-for="(contest, index) in contests"
      v-if="index < 10"
      class="list-group"
    >
      <a class="list-group-item" v-bind:href="`/arena/${contest.alias}/`">{{
        contest.title
      }}</a>
    </ul>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';

@Component
export default class Upcoming extends Vue {
  @Prop() contests!: omegaup.Contest[];

  T = T;
}
</script>
