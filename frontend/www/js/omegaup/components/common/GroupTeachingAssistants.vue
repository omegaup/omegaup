<template>
  <div class="card">
    <div class="card-body">
      <form
        class="form"
        @submit.prevent="$emit('add-group-teaching-assistant', group.key)"
      >
        <div class="form-group mb-0">
          <label class="font-weight-bold w-100"
            >{{ T.courseEditGroupTeachingAssistant }}
            <font-awesome-icon
              :title="T.courseEditAddGroupTeachingAssistantTooltip"
              icon="info-circle"
            />
            <omegaup-common-typeahead
              :existing-options="searchResultGroups"
              :value.sync="group"
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
    <div v-if="groupTeachingAssistants.length === 0">
      <div class="my-2 empty-table-message">
        {{ T.courseEditGroupTeachingAssistantsEmpty }}
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
        <tr
          v-for="groupTeachingAssistant in groupTeachingAssistants"
          :key="groupTeachingAssistant.alias"
        >
          <td>
            <a :href="`/group/${groupTeachingAssistant.alias}/edit/`">
              {{ groupTeachingAssistant.name }}
            </a>
          </td>
          <td class="text-center">{{ groupTeachingAssistant.role }}</td>
          <td class="text-center">
            <button
              v-if="groupTeachingAssistant.name !== 'teaching_assistant'"
              class="close float-none"
              type="button"
              @click="
                $emit(
                  'remove-group-teaching-assistant',
                  groupTeachingAssistant.alias,
                )
              "
            >
              ×
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
export default class GroupTeachingAssistants extends Vue {
  @Prop() groupTeachingAssistants!: types.ContestGroupAdmin[];
  @Prop() searchResultGroups!: types.ListItem[];

  T = T;
  group: null | types.ListItem = null;

  @Watch('groupTeachingAssistants')
  onGroupTeachingAssistantsChanged(): void {
    this.group = null;
  }
}
</script>
