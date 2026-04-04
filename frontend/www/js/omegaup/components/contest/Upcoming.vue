<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title">{{ T.frontPageNextContests }}</h3>
    </div>
    <ul v-for="contest in contests.slice(0, 10)" class="list-group">
      <a class="list-group-item" :href="`/arena/${contest.alias}/`">{{
        contest.title
      }}</a>
    </ul>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';

@Component
export default class Upcoming extends Vue {
  @Prop() contests!: types.ContestAdminDetails[];

  T = T;
}
</script>
