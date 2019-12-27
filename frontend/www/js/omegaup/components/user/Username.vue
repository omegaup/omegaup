<template>
  <div>
    <omegaup-countryflag v-bind:country="country"></omegaup-countryflag>
    <span v-if="linkify"
      ><a
        v-bind:class="classname"
        v-bind:title="username"
        v-bind:href="`/profile/${username}/`"
        >{{ username }}</a
      ></span
    >
    <span v-bind:class="classname" v-bind:title="username" v-else="">{{
      username
    }}</span>
  </div>
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
import CountryFlag from '../CountryFlag.vue';

@Component({
  components: {
    'omegaup-countryflag': CountryFlag,
  },
})
export default class UserName extends Vue {
  @Prop() username!: string;
  @Prop() classname!: string;
  @Prop() linkify!: boolean;
  @Prop() country!: string;
}
</script>
