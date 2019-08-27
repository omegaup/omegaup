<template>
  <div class="panel">
    <div>
      <h1><span><a class="header">{{ T.userEditChangePassword }}: {{ username }}</a></span></h1>
    </div>
    <div class="panel-body">
      <form class="form-horizontal"
            role="form"
            v-on:submit.prevent="$emit('emit-change-password', newPassword, newPasswordRepeat)">
        <div class="row">
          <div class="form-group">
            <label class="col-md-4 col-sm-4 control-label">{{ T.username }}</label>
            <div class="col-md-7 col-sm-7">
              <input class="form-control"
                   disabled="disabled"
                   size="30"
                   type="text"
                   v-bind:value="username">
            </div>
          </div>
          <div class="form-group">
            <label class="col-md-4 col-sm-4 control-label">{{ T.userEditChangePasswordNewPassword
            }}</label>
            <div class="col-md-7 col-sm-7">
              <input class="form-control"
                   size="30"
                   type="password"
                   v-model="newPassword">
            </div>
          </div>
          <div class="form-group">
            <label class="col-md-4 col-sm-4 control-label">{{
            T.userEditChangePasswordRepeatNewPassword }}</label>
            <div class="col-md-7 col-sm-7">
              <input class="form-control"
                   size="30"
                   type="password"
                   v-model="newPasswordRepeat">
            </div>
          </div>
        </div>
        <div class="form-group pull-right">
          <button class="btn btn-primary"
               type="submit">{{ T.wordsSaveChanges }}</button> <button class="btn btn-secundary"
               type="reset"
               v-on:click="$emit('emit-cancel');">{{ T.wordsCancel }}</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { T } from '../../omegaup.js';

@Component({})
export default class IdentityChangePassword extends Vue {
  @Prop() username!: string;

  T = T;
  newPassword = '';
  newPasswordRepeat = '';
}

</script>
