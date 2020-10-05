<template>
  <span :class="classname" :title="username">
    <omegaup-countryflag
      v-if="country != null"
      :country="country"
    ></omegaup-countryflag>

    <template v-if="linkify">
      <a
        v-if="emitClickEvent"
        href="#"
        :class="classname"
        :title="username"
        @click="$emit('click', username)"
        >{{ nameWithUsername }}</a
      >
      <a
        v-else
        :class="classname"
        :title="username"
        :href="`/profile/${username}/`"
        >{{ nameWithUsername }}</a
      >
    </template>
    <template v-else> {{ nameWithUsername }}</template>
  </span>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import CountryFlag from '../CountryFlag.vue';

@Component({
  components: {
    'omegaup-countryflag': CountryFlag,
  },
})
export default class Username extends Vue {
  @Prop() username!: string;
  @Prop({ default: null }) name!: string;
  @Prop() classname!: string;
  @Prop() linkify!: boolean;
  @Prop() country!: string;
  @Prop({ default: false }) emitClickEvent!: boolean;
  @Prop({ default: false }) showNameWithUsername!: boolean;

  get nameWithUsername(): string {
    if (!this.showNameWithUsername) {
      return this.name || this.username;
    }
    if (this.name) {
      return `${this.name} (${this.username})`;
    }
    return this.username;
  }
}
</script>

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
