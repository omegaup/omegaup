<template>
  <div class="card">
    <h5 class="card-title mx-2">
      {{
        ui.formatString(T.userEditChangePasswordToUsername, {
          username: username,
        })
      }}
    </h5>
    <div class="card-body">
      <form
        role="form"
        @submit.prevent="
          $emit('emit-change-password', newPassword, newPasswordRepeat)
        "
      >
        <div class="form-row">
          <div class="form-group col-lg-4 col-md-6 col-sm-6">
            <label class="d-block">
              {{ T.username }}
              <input class="form-control" :disabled="true" :value="username" />
            </label>
          </div>
          <div class="form-group col-lg-4 col-md-6 col-sm-6">
            <label class="d-block">
              {{ T.userEditChangePasswordNewPassword }}
              <input
                v-model="newPassword"
                class="form-control"
                type="password"
              />
            </label>
          </div>
          <div class="form-group col-lg-4 col-md-6 col-sm-6">
            <label class="d-block">
              {{ T.userEditChangePasswordRepeatNewPassword }}
              <input
                v-model="newPasswordRepeat"
                class="form-control"
                type="password"
              />
            </label>
          </div>
        </div>
        <div class="form-group float-right">
          <button
            class="btn btn-primary"
            type="submit"
            data-change-password-identity
          >
            {{ T.wordsSaveChanges }}
          </button>
          <button
            class="btn btn-secondary ml-2"
            type="reset"
            @click="$emit('emit-cancel')"
          >
            {{ T.wordsCancel }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import * as ui from '../../ui';
import T from '../../lang';

@Component
export default class IdentityChangePassword extends Vue {
  @Prop() username!: string;

  T = T;
  ui = ui;
  newPassword = '';
  newPasswordRepeat = '';
}
</script>
