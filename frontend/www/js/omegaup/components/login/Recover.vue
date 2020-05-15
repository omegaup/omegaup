<template>
  <div class="container-lg p-5 ">
    <h1>{{ T.passwordResetRequestTitle }}</h1>
    <form>
      <div class="form-group">
        <label for="inputEmail4" class="text-left">{{ T.profileEmail }}</label>
        <input type="text" name="email" class="form-control" v-model="email" />
      </div>
      <button
        class="btn btn-primary form-control"
        v-on:click.prevent="$emit('forgot-password', email)"
      >
        {{ T.wordsSend }}
      </button>
    </form>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';

@Component
export default class LoginPasswordRecover extends Vue {
  T = T;
  email: string = '';
}
</script>

<style>
.container-lg {
  width: 40%;
}
</style>
