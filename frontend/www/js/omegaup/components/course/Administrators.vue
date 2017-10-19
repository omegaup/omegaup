<template>
  <div class="omegaup-course-administrators panel">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-6">
          <form class="form"
                v-on:submit.prevent="onAddAdmin">
            <div class="form-group">
              <label>{{ T.wordsAdmin }}</label> <span aria-hidden="true"
                   class="glyphicon glyphicon-info-sign"
                   data-placement="top"
                   data-toggle="tooltip"
                   v-bind:title="T.courseEditAddAdminsTooltip"></span> <input autocomplete="off"
                   class="form-control typeahead"
                   name="useradmin"
                   size="20"
                   type="text"
                   v-model="useradmin">
            </div>
            <div class="form-group pull-right">
              <input id="toggle-site-admins"
                   name="toggle-site-admins"
                   type="checkbox"
                   v-model="chkSiteAdmins"> <label for="toggle-site-admins">{{
                   T.wordsShowSiteAdmins }}</label> <button class="btn btn-primary"
                   type="submit">{{ T.wordsAddAdmin }}</button> <button class="btn btn-secondary"
                   type="reset"
                   v-on:click.prevent="onCancel">{{ T.wordsCancel }}</button>
            </div>
          </form>
          <div v-if="admins.length == 0">
            <div class="empty-category">
              {{ T.courseEditAdminsEmpty }}
            </div>
          </div>
          <table class="table table-striped table-over"
                 v-else="">
            <thead>
              <tr>
                <th>{{ T.wordsUser }}</th>
                <th>{{ T.courseEditRegisteredAdminRole }}</th>
                <th class="text-right">{{ T.contestEditRegisteredAdminDelete }}</th>
              </tr>
            </thead>
            <tbody id="course-admins">
              <tr v-for="admin in admins">
                <td v-show="chkSiteAdmins || admin.role != 'site-admin'">
                  <a v-bind:href="adminProfile(admin)">{{ admin.name || admin.username }}</a>
                </td>
                <td v-show="chkSiteAdmins || admin.role != 'site-admin'">{{ admin.role }}</td>
                <td v-show="chkSiteAdmins || admin.role != 'site-admin'"><button class="close"
                        type="button"
                        v-if="admin.role != 'site-admin' &amp;&amp; admin.role != 'owner'"
                        v-on:click="onRemoveAdmin(admin)">×</button></td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="col-md-6">
          <form class="form"
                v-on:submit.prevent="onAddGroupAdmin">
            <div class="form-group">
              <label>{{ T.wordsGroupAdmin }}</label> <span aria-hidden="true"
                   class="glyphicon glyphicon-info-sign"
                   data-placement="top"
                   data-toggle="tooltip"
                   v-bind:title="T.courseEditAddGroupAdminsTooltip"></span> <input autocomplete=
                   "off"
                   class="form-control typeahead"
                   name="groupadmin"
                   size="20"
                   type="text"
                   v-model="groupadmin">
            </div>
            <div class="form-group pull-right">
              <button class="btn btn-primary"
                   type="submit">{{ T.wordsAddGroupAdmin }}</button> <button class=
                   "btn btn-secondary"
                   type="reset"
                   v-on:click.prevent="onCancel">{{ T.wordsCancel }}</button>
            </div>
          </form>
          <div v-if="groupadmins.length == 0">
            <div class="empty-category">
              {{ T.courseEditGroupAdminsEmpty }}
            </div>
          </div>
          <table class="table table-striped table-over"
                 v-else="">
            <thead>
              <tr>
                <th>{{ T.wordsGroup }}</th>
                <th class="text-right">{{ T.contestEditRegisteredAdminDelete }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="group in groupadmins">
                <td>
                  <a v-bind:href="adminGroupEdit(group)">{{ group.name || group.username }}</a>
                </td>
                <td><button class="close"
                        type="button"
                        v-on:click="onRemoveGroupAdmin(group)">×</button></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import UI from '../../ui.js';
import {T} from '../../omegaup.js';

export default {
  props: {
    admins: Array,
    groupadmins: Array,
  },
  data: function() {
    return {T: T, useradmin: '', groupadmin: '', chkSiteAdmins: false};
  },
  mounted: function() {
    let self = this;
    UI.userTypeahead(
        $('input.typeahead[name="useradmin"]', self.$el),
        function(event, item) { self.adminUsername = item.value; });
    UI.groupTypeahead($('input.typeahead[name="groupadmin"]', self.$el),
                      function(event, item) { self.adminGroup = item.value; });
  },
  methods: {
    onAddAdmin: function() {
      let hintElem = $('input.typeahead.tt-hint', this.$el);
      let hint = hintElem.val();
      if (hint) {
        // There is a hint currently visible in the UI, the user likely
        // expects that hint to be used when trying to add someone, instead
        // of what they've actually typed so far.
        this.adminUsername = hint;
      }
      this.$emit('add-admin', this.adminUsername);
    },
    onAddGroupAdmin: function() {
      let hintElem = $('input.typeahead.tt-hint', this.$el);
      let hint = hintElem.val();
      if (hint) {
        // There is a hint currently visible in the UI, the user likely
        // expects that hint to be used when trying to add someone, instead
        // of what they've actually typed so far.
        this.adminGroup = hint;
      }
      this.$emit('add-group-admin', this.adminGroup);
    },
    onCancel: function() { this.$emit('cancel');},
    onRemoveAdmin: function(admin) { this.$emit('removeAdmin', admin);},
    onRemoveGroupAdmin: function(group) {
      this.$emit('removeGroupAdmin', group);
    },
    adminProfile: function(admin) { return '/profile/' + admin.username + '/';},
    adminGroupEdit: function(admingroup) {
      return '/group/' + admingroup.alias + '/edit/';
    },
  },
};
</script>
