<template>
  <div class="card mb-3 panel panel-primary">
    <div class="card-body panel-body">
      <form class="form" @submit.prevent="onSubmit">
        <div class="form-group">
          <label class="font-weight-bold"
            >{{ T.courseEditTeachingAssistants }}
            <font-awesome-icon
              :title="T.courseEditAddTeachingAssistantsTooltip"
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
              {{ T.courseEditAddTeachingAssistants }}
            </button>
          </div>
        </div>
      </form>
    </div>
    <div v-if="teachingAssistants.length === 0">
      <div class="empty-table-message">
        {{ T.courseEditTeachingAssistantsEmpty }}
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
        <template v-for="teachingAssistant in teachingAssistants">
          <tr
            v-if="teachingAssistant.role !== 'teaching-assistant'"
            :key="teachingAssistant.username"
          >
            <td>
              <omegaup-user-username
                :linkify="true"
                :username="teachingAssistant.username"
              ></omegaup-user-username>
            </td>
            <td>{{ teachingAssistant.role }}</td>
            <td>
              <button
                v-if="teachingAssistant.role === 'teaching_assistant'"
                type="button"
                class="close"
                @click="onRemove(teachingAssistant)"
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
export default class TeachingAssistants extends Vue {
  @Prop() initialTeachingAssistants!: omegaup.UserRole[];
  @Prop() searchResultUsers!: types.ListItem[];

  T = T;
  username: null | types.ListItem = null;
  selected = {};
  teachingAssistants = this.initialTeachingAssistants;

  @Watch('initialTeachingAssistants')
  onTeachingAssistantsChanged(newValue: omegaup.UserRole[]): void {
    this.teachingAssistants = newValue;
  }

  onSubmit(): void {
    this.$emit('add-teaching-assistant', this.username?.key);
    this.username = null;
  }

  onRemove(teachingAssistant: omegaup.UserRole): void {
    this.$emit('remove-teaching-assistant', teachingAssistant.username);
  }
}
</script>
