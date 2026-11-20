<template>
  <form @submit.prevent="onAddPassword">
    <div class="form-group">
      <label>{{ T.profileUsername }}</label>
      <div>
        <input
          v-model="newUsername"
          data-username
          size="30"
          required
          class="form-control"
        />
      </div>
    </div>
    <div class="form-group">
      <label>{{ T.userEditChangePasswordNewPassword }}</label>
      <div>
        <omegaup-password-input
          v-model="newPassword"
          data-new-password
          :size="30"
          :required="true"
          autocomplete="new-password"
        />
      </div>
    </div>
    <div class="form-group">
      <label>{{ T.userEditChangePasswordRepeatNewPassword }}</label>
      <div>
        <omegaup-password-input
          v-model="newPassword2"
          data-new-password2
          :size="30"
          :required="true"
          :input-class="invalidPasswordClass"
          autocomplete="new-password"
        />
        <div v-if="passwordMismatch" class="invalid-message">
          {{ T.passwordMismatch }}
        </div>
      </div>
    </div>
    <div>
      <button
        type="submit"
        class="btn btn-primary mr-2"
        :disabled="submitDisabled"
      >
        {{ T.wordsSaveChanges }}
      </button>
      <a href="/profile/" class="btn btn-cancel">{{ T.wordsCancel }}</a>
    </div>
  </form>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import T from '../../lang';
import omegaup_PasswordInput from '../common/PasswordInput.vue';

export default defineComponent({
  name: 'UserPasswordAdd',
  components: {
    'omegaup-password-input': omegaup_PasswordInput,
  },
  props: {
    username: {
      type: String,
      required: true,
    },
  },
  emits: ['add-password'],
  data(): {
    T: typeof T;
    newUsername: string;
    newPassword: string;
    newPassword2: string;
  } {
    return {
      T,
      newUsername: this.username,
      newPassword: '',
      newPassword2: '',
    };
  },
  computed: {
    passwordMismatch(): boolean {
      return this.newPassword !== this.newPassword2;
    },
    invalidPasswordClass(): string {
      return this.passwordMismatch ? 'invalid-input' : '';
    },
    submitDisabled(): boolean {
      return (
        this.passwordMismatch ||
        this.newPassword.length === 0 ||
        this.newPassword2.length === 0 ||
        this.newUsername.trim().length === 0
      );
    },
  },
  watch: {
    username(newValue: string): void {
      this.newUsername = newValue;
    },
  },
  methods: {
    onAddPassword(): void {
      if (this.passwordMismatch) {
        return;
      }
      this.$emit('add-password', {
        username: this.newUsername,
        password: this.newPassword,
      });
    },
  },
});
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
.invalid-input {
  border-color: var(--form-input-error-color);
}

.invalid-input:focus {
  box-shadow: 0 0 0 0.2rem var(--form-input-box-shadow-error-color);
}

.invalid-message {
  margin-top: 0.25rem;
  font-size: 80%;
  color: var(--form-input-error-color);
}
</style>
