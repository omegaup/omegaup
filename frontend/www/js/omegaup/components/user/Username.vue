<template>
  <span :class="classname" :title="username">
    <omegaup-countryflag
      v-if="country != null"
      :country="country"
    ></omegaup-countryflag>

    <template v-if="linkify">
      <a
        v-if="!!$listeners['emit-click']"
        href="#"
        :class="classname"
        :title="username"
        @click="$emit('emit-click', username)"
        >{{ name || username }}</a
      >
      <a
        v-else
        :class="classname"
        :title="username"
        :href="`/profile/${username}/`"
        >{{ name || username }}</a
      >
    </template>
    <template v-else> {{ name || username }}</template>
  </span>
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
  @Prop({ default: null }) name!: string;
  @Prop() classname!: string;
  @Prop() linkify!: boolean;
  @Prop() country!: string;
}
</script>
