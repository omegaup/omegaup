<template>
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">{{ T.userEditChangePassword }}</h3>
    </div>
    <div class="card-body">
      <form @submit.prevent="onUpdatePassword">
        <div class="form-group">
          <label>{{ T.userEditChangePasswordOldPassword }}</label>
          <div>
            <input
              v-model="oldPassword"
              data-old-password
              type="password"
              size="30"
              required
              class="form-control"
            />
          </div>
        </div>
        <div class="form-group">
          <label>{{ T.userEditChangePasswordNewPassword }}</label>
          <div>
            <input
              v-model="newPassword"
              data-new-password
              type="password"
              size="30"
              required
              class="form-control"
            />
          </div>
        </div>
        <div class="form-group">
          <label>{{ T.userEditChangePasswordRepeatNewPassword }}</label>
          <div>
            <input
              v-model="newPassword2"
              data-new-password2
              type="password"
              size="30"
              required
              class="form-control"
              :class="invalidPasswordClass"
            />
            <div v-if="passwordMismatch" class="invalid-message">
              {{ T.passwordMismatch }}
            </div>
          </div>
        </div>
        <div>
          <button type="submit" class="btn btn-primary mr-2">
            {{ T.wordsSaveChanges }}
          </button>
          <a href="/profile/" class="btn btn-cancel">{{ T.wordsCancel }}</a>
        </div>
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Watch } from 'vue-property-decorator';
import T from '../../lang';

@Component({
  components: {},
})
export default class UserPasswordEdit extends Vue {
  T = T;
  oldPassword = '';
  newPassword = '';
  newPassword2 = '';
  passwordMismatch = false;

  get invalidPasswordClass(): string {
    return this.passwordMismatch ? 'invalid-input' : '';
  }

  onUpdatePassword(): void {
    if (this.newPassword !== this.newPassword2) {
      this.passwordMismatch = true;
      return;
    }
    this.$emit('update-password', {
      oldPassword: this.oldPassword,
      newPassword: this.newPassword,
    });
  }

  @Watch('newPassword2')
  onNewPassword2Changed(): void {
    if (this.passwordMismatch) {
      this.passwordMismatch = false;
    }
  }
}
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
