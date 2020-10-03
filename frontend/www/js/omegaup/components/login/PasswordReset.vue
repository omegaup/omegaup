<template>
  <div class="card">
    <h2 class="card-header">{{ T.passwordResetResetTitle }}</h2>
    <div class="card-body">
      <form>
        <div class="form-group">
          <h5>{{ T.passwordResetPassword }}</h5>
          <input
            type="password"
            name="password"
            class="form-control"
            v-model="password"
          />
        </div>
        <div class="form-group">
          <h5>{{ T.passwordResetPasswordConfirmation }}</h5>
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
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
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
