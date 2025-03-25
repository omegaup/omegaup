<template>
  <div class="card">
    <div class="card-body">
      <form
        class="form"
        @submit.prevent="$emit('add-group-admin', groupAlias.key)"
      >
        <div class="form-group mb-0">
          <label class="font-weight-bold w-100"
            >{{ T.wordsGroupAdmin }}
            <font-awesome-icon
              :title="T.courseEditAddGroupAdminsTooltip"
              icon="info-circle"
            />
            <omegaup-common-typeahead
              :existing-options="searchResultGroups"
              :value.sync="groupAlias"
              :max-results="10"
              @update-existing-options="
                (query) => $emit('update-search-result-groups', query)
              "
            ></omegaup-common-typeahead>
          </label>
        </div>
        <button class="btn btn-primary" type="submit">
          {{ T.contestAddgroupAddGroup }}
        </button>
      </form>
    </div>
    <div v-if="groupAdmins.length === 0">
      <div class="my-2 empty-table-message">
        {{ T.courseEditGroupAdminsEmpty }}
      </div>
    </div>
    <table v-else class="table table-striped mb-0">
      <thead>
        <tr>
          <th class="text-center">
            {{ T.contestEditRegisteredGroupAdminName }}
          </th>
          <th class="text-center">{{ T.contestEditRegisteredAdminRole }}</th>
          <th class="text-center">{{ T.contestEditRegisteredAdminDelete }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="groupAdmin in groupAdmins" :key="groupAdmin.alias">
          <td>
            <a :href="`/group/${groupAdmin.alias}/edit/`">
              {{ groupAdmin.name }}
            </a>
          </td>
          <td class="text-center">{{ groupAdmin.role }}</td>
          <td class="text-center">
            <button
              v-if="groupAdmin.name !== 'admin'"
              class="close float-none"
              type="button"
              @click="$emit('remove-group-admin', groupAdmin.alias)"
            >
              <font-awesome-icon :icon="['fas', 'trash']" size="xs" />
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import common_Typeahead from '../common/Typeahead.vue';

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
    'font-awesome-icon': FontAwesomeIcon,
    'font-awesome-layers': FontAwesomeLayers,
    'font-awesome-layers-text': FontAwesomeLayersText,
  },
})
export default class GroupAdmin extends Vue {
  @Prop() groupAdmins!: types.ContestGroupAdmin[];
  @Prop() searchResultGroups!: types.ListItem[];

  T = T;
  groupAlias: null | types.ListItem = null;

  @Watch('groupAdmins')
  ongroupAdminsChange(): void {
    this.groupAlias = null;
  }
}
</script>
