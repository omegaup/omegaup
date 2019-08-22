<template>
  <div class="omegaup-course-administrators panel">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-6">
          <form class="form"
                v-on:submit.prevent="$emit('add-admin', adminUsername)">
            <div class="form-group">
              <label>{{ T.wordsAdmin }}</label> <span aria-hidden="true"
                   class="glyphicon glyphicon-info-sign"
                   data-placement="top"
                   data-toggle="tooltip"
                   v-bind:title="T.courseEditAddAdminsTooltip"></span>
                   <omegaup-autocomplete v-bind:init="el =&gt; UI.userTypeahead(el)"
                   v-model="adminUsername"></omegaup-autocomplete>
            </div>
            <div class="form-group pull-right">
              <label><input name="toggle-site-admins"
                     type="checkbox"
                     v-model="showSiteAdmins"> {{ T.wordsShowSiteAdmins }}</label> <button class=
                     "btn btn-primary"
                   type="submit">{{ T.wordsAddAdmin }}</button> <button class="btn btn-secondary"
                   type="reset"
                   v-on:click.prevent="$emit('cancel')">{{ T.wordsCancel }}</button>
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
            <tbody>
              <tr v-for="admin in admins"
                  v-show="showSiteAdmins || admin.role != 'site-admin'">
                <td>
                  <a v-bind:href="adminProfile(admin)">{{ admin.name || admin.username }}</a>
                </td>
                <td>{{ admin.role }}</td>
                <td><button class="close"
                        type="button"
                        v-if="admin.role != 'site-admin' &amp;&amp; admin.role != 'owner'"
                        v-on:click="$emit('removeAdmin', admin)">×</button></td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="col-md-6">
          <form class="form"
                v-on:submit.prevent="$emit('add-group-admin', adminGroup)">
            <div class="form-group">
              <label>{{ T.wordsGroupAdmin }}</label> <span aria-hidden="true"
                   class="glyphicon glyphicon-info-sign"
                   data-placement="top"
                   data-toggle="tooltip"
                   v-bind:title="T.courseEditAddGroupAdminsTooltip"></span>
                   <omegaup-autocomplete v-bind:init="el =&gt; UI.userTypeahead(el)"
                   v-model="adminGroup"></omegaup-autocomplete>
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
                  <a v-bind:href="adminGroupEdit(group)">{{ group.name }}</a>
                </td>
                <td><button class="close"
                        type="button"
                        v-on:click="$emit('removeGroupAdmin', group)">×</button></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import omegaup from '../../api.js';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';
import Autocomplete from '../Autocomplete.vue';

@Component({
  components: {
    'omegaup-autocomplete': Autocomplete,
  },
})
export default class CourseAdministrators extends Vue {
  @Prop() admins!: omegaup.CourseAdmin[];
  @Prop() groupadmins!: omegaup.CourseGroupAdmin[];

  T = T;
  UI = UI;
  showSiteAdmins = false;
  adminUsername = '';
  adminGroup = '';

  adminProfile(admin: omegaup.CourseAdmin): string {
    return `/profile/${admin.username}/`;
  }

  adminGroupEdit(admingroup: omegaup.CourseGroupAdmin): string {
    return `/group/${admingroup.alias}/edit/`;
  }
}

</script>
