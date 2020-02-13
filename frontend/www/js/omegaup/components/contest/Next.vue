<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title">{{ T.frontPageNextContests }}</h3>
    </div>
    <ul class="list-group" v-for="contest in contests">
      <a class="list-group-item" v-bind:href="contest.alias">{{
        contest.title
      }}</a>
    </ul>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import omegaup from '../../api.js';

@Component
export default class SocialMedia extends Vue {
  @Prop() contests!: omegaup.Contest[];

  T = T;
}
</script>
