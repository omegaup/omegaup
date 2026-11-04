<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h2 class="panel-title">{{ T.userEditAddPassword }}</h2>
    </div>
    <div class="panel-body">
      <form class="form-horizontal" role="form" @submit.prevent="formSubmit">
        <div class="form-group">
          <label class="col-md-3 control-label" for="username">{{
            T.profileUsername
          }}</label>
          <div class="col-md-7">
            <input
              v-model="username"
              class="form-control"
              name="username"
              size="30"
              type="text"
            />
          </div>
        </div>
        <div class="form-group">
          <label class="col-md-3 control-label" for="new-password-1">{{
            T.userEditChangePasswordNewPassword
          }}</label>
          <div class="col-md-7">
            <omegaup-password-input
              v-model="newPassword1"
              name="new-password-1"
              :size="30"
              autocomplete="new-password"
            />
          </div>
        </div>
        <div class="form-group">
          <label class="col-md-3 control-label" for="new-password-2">{{
            T.userEditChangePasswordRepeatNewPassword
          }}</label>
          <div class="col-md-7">
            <omegaup-password-input
              v-model="newPassword2"
              name="new-password-2"
              :size="30"
              autocomplete="new-password"
            />
          </div>
        </div>
        <div class="form-group">
          <div class="col-md-offset-3 col-md-7">
            <button class="btn btn-primary" type="submit">
              {{ T.wordsSaveChanges }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import T from '../../lang';
import * as ui from '../../ui';
import omegaup_PasswordInput from '../common/PasswordInput.vue';

export default defineComponent({
  name: 'UserBasicEdit',
  components: {
    'omegaup-password-input': omegaup_PasswordInput,
  },
  props: {
    initialUsername: {
      type: String,
      default: '',
    },
  },
  emits: ['update'],
  data(): {
    T: typeof T;
    username: string;
    newPassword1: string;
    newPassword2: string;
  } {
    return {
      T,
      username:
        this.initialUsername ||
        ((this.$attrs.username as string | undefined) ?? ''),
      newPassword1: '',
      newPassword2: '',
    };
  },
  watch: {
    initialUsername(val: string): void {
      this.username = val;
    },
  },
  methods: {
    formSubmit(): void {
      if (this.newPassword1 !== this.newPassword2) {
        ui.error(T.userPasswordMustBeSame);
        return;
      }
      this.$emit('update', this.username, this.newPassword1);
    },
  },
});
</script>
