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
              v-model="newPassword1"
              data-new-password1
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
            />
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
import { Vue, Component } from 'vue-property-decorator';
import T from '../../lang';

@Component({
  components: {},
})
export default class UserPasswordEdit extends Vue {
  T = T;
  oldPassword = '';
  newPassword1 = '';
  newPassword2 = '';

  onUpdatePassword(): void {
    this.$emit(
      'update-password',
      this.oldPassword,
      this.newPassword1,
      this.newPassword2,
    );
  }
}
</script>
