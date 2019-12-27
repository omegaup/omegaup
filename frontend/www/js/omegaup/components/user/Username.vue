<template>
  <span v-if="linkify"
    ><a v-bind:class="classname" v-bind:href="`/profile/${username}/`">{{
      username
    }}</a></span
  >
  <span v-bind:class="classname" v-else="">{{ username }}</span>
</template>

<style>
.user-rank-unranked,
.user-rank-beginner,
.user-rank-specialist,
.user-rank-expert,
.user-rank-master,
.user-rank-international-master {
  font-weight: bold;
}

.user-rank-beginner {
  color: #919191;
}

.user-rank-specialist {
  color: #598c4c;
}

.user-rank-expert {
  color: #1c52c7;
}

.user-rank-master {
  color: #f0c245;
}

.user-rank-international-master {
  color: #cb000a;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';

@Component
export default class UserName extends Vue {
  @Prop() username!: string;
  @Prop() classname!: string;
  @Prop() linkify!: boolean;
}
</script>
