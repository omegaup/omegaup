<template>
  <div class="container-lg p-5 w-75">
    <div class="card">
      <h2 class="card-header">{{ T.passwordResetResetTitle }}</h2>
      <div class="card-body">
        <form>
          <div class="form-group">
            <label for="password">{{ T.passwordResetPassword }}</label>
            <input
              type="password"
              name="password"
              class="form-control"
              v-model="password"
            />
          </div>
          <div class="form-group">
            <label for="password_confirmation">{{
              T.passwordResetPasswordConfirmation
            }}</label>
            <input
              type="password"
              name="password_confirmation"
              class="form-control"
              v-model="passwordConfirmation"
            />
          </div>
          <div class="form-group">
            <button
              type="button"
              class="btn btn-primary form-control"
              v-on:click.prevent="
                $emit(
                  'reset-password',
                  email,
                  resetToken,
                  password,
                  passwordConfirmation,
                )
              "
            >
              {{ T.wordsSaveChanges }}
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
export default class PasswordRecover extends Vue {
  @Prop() email?: string;
  @Prop() resetToken?: string;

  T = T;
  password = '';
  passwordConfirmation = '';
}
</script>
