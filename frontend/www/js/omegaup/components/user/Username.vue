<template>
  <span v-if="linkify"
    ><a v-bind:class="classname" v-bind:href="`/profile/${username}/`">{{
      username
    }}</a></span
  >
  <span v-bind:class="classname" v-else="">{{ username }}</span>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';

@Component
export default class UserName extends Vue {
  @Prop() username!: string;
  @Prop() classname!: string;
  @Prop() linkify!: boolean;
}
</script>
