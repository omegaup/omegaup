<template>
  <div class="card mb-3">
    <div class="card-body">
      <form class="form" @submit.prevent="$emit('add-admin', username)">
        <div class="form-group mb-0">
          <label
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
        <div class="form-group mb-0">
          <label>
            <input
              v-model="showSiteAdmins"
              type="checkbox"
              name="toggle-site-admins"
            />
            {{ T.wordsShowSiteAdmins }}
          </label>
        </div>
        <button class="btn btn-primary" type="submit">
          {{ T.wordsAddAdmin }}
        </button>
      </form>
    </div>
    <div v-if="admins.length === 0">
      <div class="my-2 empty-table-message">
        {{ T.courseEditAdminsEmpty }}
      </div>
    </div>
    <table v-else class="table table-striped mb-0">
      <thead>
        <tr>
          <th class="text-center">
            {{ T.contestEditRegisteredAdminUsername }}
          </th>
          <th class="text-center">{{ T.contestEditRegisteredAdminRole }}</th>
          <th class="text-center">{{ T.contestEditRegisteredAdminDelete }}</th>
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
            <td class="text-center">{{ admin.role }}</td>
            <td class="text-center">
              <button
                v-if="admin.role === 'admin'"
                type="button"
                class="close float-none"
                @click="$emit('remove-admin', admin.username)"
              >
                ×
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
import { types } from '../../api_types';
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
  @Prop() admins!: types.ContestAdmin[];
  @Prop() searchResultUsers!: types.ListItem[];

  T = T;
  username: null | string = null;
  showSiteAdmins = false;

  @Watch('admins')
  onAdminsChange(): void {
    this.username = null;
  }
}
</script>
