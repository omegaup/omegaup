<template>
  <div>
    <div class="panel panel-primary">
      <div class="panel-body">
        <form class="form" v-on:submit.prevent="$emit('add-admin', username)">
          <div class="form-group">
            <label>
              {{ T.wordsAdmin }}
              <omegaup-autocomplete
                class="form-control"
                v-bind:init="el => typeahead.userTypeahead(el)"
                v-model="username"
              ></omegaup-autocomplete>
            </label>
          </div>

          <div class="form-group">
            <div class="col-xs-5 col-sm-3 col-md-3 action-container">
              <button class="btn btn-primary" type="submit">
                {{ T.wordsAddAdmin }}
              </button>
            </div>
            <div class="col-xs-7 col-sm-9 col-md-9 toggle-container">
              <label>
                <input
                  type="checkbox"
                  name="toggle-site-admins"
                  v-model="showSiteAdmins"
                />
                {{ T.wordsShowSiteAdmins }}
              </label>
            </div>
          </div>
        </form>
      </div>

      <table class="table table-striped">
        <thead>
          <tr>
            <th>{{ T.contestEditRegisteredAdminUsername }}</th>
            <th>{{ T.contestEditRegisteredAdminRole }}</th>
            <th>{{ T.contestEditRegisteredAdminDelete }}</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-bind:class="{
              hidden: admin.role === 'site-admin' && !showSiteAdmins,
            }"
            v-for="admin in admins"
          >
            <td>
              <a v-bind:href="`/profile/${admin.username}/`">{{
                admin.username
              }}</a>
            </td>
            <td>{{ admin.role }}</td>
            <td>
              <button
                type="button"
                class="close"
                v-if="admin.role === 'admin'"
                v-on:click="$emit('remove-admin', admin.username)"
              >
                &times;
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="panel panel-primary">
      <div class="panel-body">
        <form
          class="form"
          v-on:submit.prevent="$emit('add-group-admin', groupAlias)"
        >
          <div class="form-group">
            <label>
              {{ T.wordsGroupAdmin }}
              <omegaup-autocomplete
                class="form-control"
                v-bind:init="
                  el =>
                    typeahead.groupTypeahead(
                      el,
                      (ev, val) => (groupAlias = val.value),
                    )
                "
                v-model="groupAlias"
              ></omegaup-autocomplete>
            </label>
          </div>

          <button class="btn btn-primary" type="submit">
            {{ T.contestAddgroupAddGroup }}
          </button>
        </form>
      </div>

      <table class="table table-striped">
        <thead>
          <tr>
            <th>{{ T.contestEditRegisteredGroupAdminName }}</th>
            <th>{{ T.contestEditRegisteredAdminRole }}</th>
            <th>{{ T.contestEditRegisteredAdminDelete }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="groupAdmin in groupAdmins">
            <td>
              <a v-bind:href="`/group/${groupAdmin.alias}/edit/`">
                {{ groupAdmin.name }}
              </a>
            </td>
            <td>{{ groupAdmin.role }}</td>
            <td>
              <button
                type="button"
                class="close"
                v-on:click="$emit('remove-group-admin', groupAdmin.alias)"
              >
                &times;
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import T from '../../lang';
import * as typeahead from '../../typeahead';
import * as ui from '../../ui';
import { types } from '../../api_types';
import Autocomplete from '../Autocomplete.vue';

@Component({
  components: {
    'omegaup-autocomplete': Autocomplete,
  },
})
export default class ProblemAdmins extends Vue {
  @Prop() admins!: types.ProblemAdmin[];
  @Prop() groupAdmins!: types.ProblemGroupAdmin[];

  T = T;
  typeahead = typeahead;
  showSiteAdmins = false;
  username = '';
  groupAlias = '';
}
</script>
