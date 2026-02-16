<template>
  <div class="card">
    <h2 class="card-header">{{ T.passwordResetResetTitle }}</h2>
    <div class="card-body">
      <form>
        <div class="form-group">
          <h5>{{ T.passwordResetPassword }}</h5>
          <omegaup-password-input
            v-model="password"
            name="password"
            autocomplete="new-password"
          />
        </div>
        <div class="form-group">
          <h5>{{ T.passwordResetPasswordConfirmation }}</h5>
          <omegaup-password-input
            v-model="passwordConfirmation"
            name="password_confirmation"
            autocomplete="new-password"
          />
        </div>
        <div class="form-group">
          <button
            type="button"
            class="btn btn-primary form-control"
            @click.prevent="
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
import omegaup_PasswordInput from '../common/PasswordInput.vue';

@Component({
  components: {
    'omegaup-password-input': omegaup_PasswordInput,
  },
})
export default class PasswordRecover extends Vue {
  @Prop() email?: string;
  @Prop() resetToken?: string;

  T = T;
  password = '';
  passwordConfirmation = '';
}
</script>
