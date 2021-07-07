<template>
  <div class="card mb-3 panel panel-primary">
    <div class="card-body panel-body">
      <form class="form" @submit.prevent="onSubmit">
        <div class="form-group">
          <label class="font-weight-bold"
            >{{ T.wordsAdmin }}
            <font-awesome-icon
              :title="T.courseEditAddAdminsTooltip"
              icon="info-circle"
            />
            <omegaup-common-typeahead
              :existing-options="searchResultUsers"
              :value.sync="username"
              :max-results="10"
              @update-existing-options="
                (query) => $emit('update-search-result-users', query)
              "
            />
          </label>
        </div>
        <div class="row">
          <div class="action-container col-md-6">
            <button class="btn btn-primary" type="submit">
              {{ T.wordsAddAdmin }}
            </button>
          </div>
          <div class="toggle-container col-md-6">
            <label class="font-weight-bold">
              <input
                v-model="showSiteAdmins"
                type="checkbox"
                name="toggle-site-admins"
              />
              {{ T.wordsShowSiteAdmins }}
            </label>
          </div>
        </div>
      </form>
    </div>
    <div v-if="admins.length === 0">
      <div class="empty-table-message">
        {{ T.courseEditAdminsEmpty }}
      </div>
    </div>
    <table v-else class="table table-striped">
      <thead>
        <tr>
          <th>{{ T.contestEditRegisteredAdminUsername }}</th>
          <th>{{ T.contestEditRegisteredAdminRole }}</th>
          <th>{{ T.contestEditRegisteredAdminDelete }}</th>
        </tr>
      </thead>
      <tbody>
        <template v-for="admin in admins">
          <tr
            v-if="admin.role !== 'site-admin' || showSiteAdmins"
            :key="admin.username"
          >
            <td>
              <omegaup-user-username
                :linkify="true"
                :username="admin.username"
              ></omegaup-user-username>
            </td>
            <td>{{ admin.role }}</td>
            <td>
              <button
                v-if="admin.role === 'admin'"
                type="button"
                class="close"
                @click="onRemove(admin)"
              >
                &times;
              </button>
            </td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import common_Typeahead from '../common/Typeahead.vue';
import user_Username from '../user/Username.vue';

import {
  FontAwesomeIcon,
  FontAwesomeLayers,
  FontAwesomeLayersText,
} from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
import { types } from '../../api_types';
library.add(fas);

@Component({
  components: {
    'omegaup-common-typeahead': common_Typeahead,
    'omegaup-user-username': user_Username,
    'font-awesome-icon': FontAwesomeIcon,
    'font-awesome-layers': FontAwesomeLayers,
    'font-awesome-layers-text': FontAwesomeLayersText,
  },
})
export default class Admins extends Vue {
  @Prop() initialAdmins!: omegaup.UserRole[];
  @Prop({ default: false }) hasParentComponent!: boolean;
  @Prop() searchResultUsers!: types.ListItem[];

  T = T;
  username: null | string = null;
  showSiteAdmins = false;
  selected = {};
  admins = this.initialAdmins;

  @Watch('initialAdmins')
  onAdminsChanged(newValue: omegaup.UserRole[]): void {
    this.admins = newValue;
  }

  onSubmit(): void {
    if (this.hasParentComponent) {
      this.$emit('emit-add-admin', this);
      this.username = null;
      return;
    }
    this.$emit('add-admin', this.username);
    this.username = null;
  }

  onRemove(admin: omegaup.UserRole): void {
    if (this.hasParentComponent) {
      this.selected = admin;
      this.$emit('emit-remove-admin', this);
      return;
    }
    this.$emit('remove-admin', admin.username);
  }
}
</script>

<style lang="scss">
@import '../../../../sass/main.scss';
</style>
