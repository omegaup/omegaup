<template>
  <div class="container">
    <h1>{{ T.passwordResetRequestTitle }}</h1>
    <div class="row">
      <div class="col-md-6 col-md-offset-3">
        <form>
          <div class="form-group">
            <label>{{ T.profileEmail }}</label>
            <input
              type="text"
              name="email"
              class="form-control"
              v-model="email"
            />
          </div>
          <div class="form-group">
            <button
              class="btn btn-primary form-control"
              v-on:click.prevent="$emit('forgot-password', email)"
            >
              {{ T.wordsSend }}
            </button>
          </div>
        </form>
      </div>
    </div>
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
