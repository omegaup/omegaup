<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h2 class="panel-title">{{ T.userEditAddPassword }}</h2>
    </div>
    <div class="panel-body">
      <form class="form-horizontal"
            role="form"
            v-on:submit.prevent="formSubmit">
        <div class="form-group">
          <label class="col-md-3 control-label"
               for="username">{{ T.profileUsername }}</label>
          <div class="col-md-7">
            <input class="form-control"
                 name="username"
                 size="30"
                 type="text"
                 v-model="username">
          </div>
        </div>
        <div class="form-group">
          <label class="col-md-3 control-label"
               for="new-password-1">{{ T.userEditChangePasswordNewPassword }}</label>
          <div class="col-md-7">
            <input class="form-control"
                 name="new-password-1"
                 size="30"
                 type="password"
                 v-model="newPassword1">
          </div>
        </div>
        <div class="form-group">
          <label class="col-md-3 control-label"
               for="new-password-2">{{ T.userEditChangePasswordRepeatNewPassword }}</label>
          <div class="col-md-7">
            <input class="form-control"
                 name="new-password-2"
                 size="30"
                 type="password"
                 v-model="newPassword2">
          </div>
        </div>
        <div class="form-group">
          <div class="col-md-offset-3 col-md-7">
            <button class="btn btn-primary"
                 type="submit">{{ T.wordsSaveChanges }}</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import {OmegaUp, T, API} from '../../omegaup.js';
export default {
  data: function() {
    return { T: T, newPassword1: '', newPassword2: '', username: '', }
  },
  mounted: function() {
    var self = this;
    omegaup.API.User.profile()
        .then(function(data) { self.username = data.userinfo.username; })
        .fail(omegaup.UI.apiError);
  },
  methods: {
    formSubmit: function() {
      var self = this;
      if (self.newPassword1 != self.newPassword2) {
        omegaup.UI.error(T.userPasswordMustBeSame);
        return false;
      }

      omegaup.API.User.updateBasicInfo({
                        username: self.username,
                        password: self.newPassword1,
                      })
          .then(function(response) { window.location = '/profile/'; })
          .fail(omegaup.UI.apiError);
    },
  }
}

</script>
