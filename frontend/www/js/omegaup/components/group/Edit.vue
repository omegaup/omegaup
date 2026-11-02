<template>
  <div class="group-edit">
    <div class="page-header">
      <h2>
        {{ ui.formatString(T.groupEditTitleWithName, { name: groupName }) }}
      </h2>
    </div>
    <ul class="nav nav-pills mt-4">
      <li class="nav-item" role="presentation">
        <a
          :href="`#${AvailableTabs.Edit}`"
          class="nav-link"
          data-tab-edit
          :class="{ active: selectedTab === AvailableTabs.Edit }"
          @click="selectedTab = AvailableTabs.Edit"
          >{{ T.groupEditEdit }}</a
        >
      </li>
      <li class="nav-item" role="presentation">
        <a
          :href="`#${AvailableTabs.Members}`"
          class="nav-link"
          data-tab-members
          :class="{ active: selectedTab === AvailableTabs.Members }"
          @click="selectedTab = AvailableTabs.Members"
          >{{ T.groupEditMembers }}</a
        >
      </li>
      <li class="nav-item" role="presentation">
        <a
          :href="`#${AvailableTabs.Scoreboards}`"
          class="nav-link"
          data-tab-scoreboards
          :class="{ active: selectedTab === AvailableTabs.Scoreboards }"
          @click="selectedTab = AvailableTabs.Scoreboards"
          >{{ T.groupEditScoreboards }}</a
        >
      </li>
      <li class="nav-item" role="presentation">
        <a
          :href="`#${AvailableTabs.Identities}`"
          class="nav-link"
          data-tab-identities
          :class="{ active: selectedTab === AvailableTabs.Identities }"
          @click="selectedTab = AvailableTabs.Identities"
          >{{ T.groupCreateIdentities }}</a
        >
      </li>
    </ul>

    <div class="tab-content">
      <div
        v-if="selectedTab === AvailableTabs.Edit"
        class="tab-pane active"
        role="tabpanel"
      >
        <omegaup-group-form
          :is-update="true"
          :group-name="groupName"
          :group-alias="groupAlias"
          :group-description="groupDescription"
          @update-group="
            (name, description) => $emit('update-group', name, description)
          "
        ></omegaup-group-form>
      </div>

      <div
        v-if="selectedTab === AvailableTabs.Members"
        class="tab-pane active"
        role="tabpanel"
      >
        <omegaup-group-members
          :identities="currentIdentities"
          :identities-csv="currentIdentitiesCsv"
          :group-alias="groupAlias"
          :countries="countries"
          :search-result-users="searchResultUsers"
          :search-result-schools="searchResultSchools"
          @update-search-result-schools="
            (query) => $emit('update-search-result-schools', query)
          "
          @add-member="
            (memberComponent, username) =>
              $emit('add-member', memberComponent, username)
          "
          @edit-identity="
            (memberComponent, identity) =>
              $emit('edit-identity', memberComponent, identity)
          "
          @edit-identity-member="
            (request) => $emit('edit-identity-member', request)
          "
          @change-password-identity="
            (memberComponent, username) =>
              $emit('change-password-identity', memberComponent, username)
          "
          @change-password-identity-member="
            (memberComponent, username, password, repeatPassword) =>
              $emit(
                'change-password-identity-member',
                memberComponent,
                username,
                password,
                repeatPassword,
              )
          "
          @remove="(username) => $emit('remove', username)"
          @cancel="(memberComponent) => $emit('cancel', memberComponent)"
          @update-search-result-users="
            (query) => $emit('update-search-result-users', query)
          "
        ></omegaup-group-members>
      </div>

      <div
        v-if="selectedTab === AvailableTabs.Scoreboards"
        class="tab-pane active"
        role="tabpanel"
      >
        <omegaup-group-scoreboards
          :group-alias="groupAlias"
          :scoreboards.sync="currentScoreboards"
          @create-scoreboard="
            (title, alias, description) =>
              $emit('create-scoreboard', title, alias, description)
          "
        ></omegaup-group-scoreboards>
      </div>

      <div
        v-if="selectedTab === AvailableTabs.Identities"
        class="tab-pane active"
        role="tabpanel"
      >
        <omegaup-group-create-identities
          :group-alias="groupAlias"
          :user-error-row="userErrorRow"
          :has-visited-section="hasVisitedSection"
          :is-organizer="isOrganizer"
          @bulk-identities="
            (identities) => $emit('bulk-identities', identities)
          "
          @download-identities="
            (identities) => $emit('download-identities', identities)
          "
          @read-csv="(source) => $emit('read-csv', source)"
          @invalid-file="$emit('invalid-file')"
        ></omegaup-group-create-identities>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import type { PropType } from 'vue';
import group_Identities from './Identities.vue';
import group_Form from './Form.vue';
import group_Members from './Members.vue';
import group_Scoreboards from './Scoreboards.vue';
import T from '../../lang';
import { dao, types } from '../../api_types';
import * as ui from '../../ui';

export enum AvailableTabs {
  Edit = 'edit',
  Members = 'members',
  Scoreboards = 'scoreboards',
  Identities = 'identities',
}

export default defineComponent({
  name: 'GroupEdit',
  components: {
    'omegaup-group-create-identities': group_Identities,
    'omegaup-group-form': group_Form,
    'omegaup-group-members': group_Members,
    'omegaup-group-scoreboards': group_Scoreboards,
  },
  props: {
    groupAlias: {
      type: String,
      required: true,
    },
    groupDescription: {
      type: String,
      required: true,
    },
    groupName: {
      type: String,
      required: true,
    },
    countries: {
      type: Array as PropType<dao.Countries[]>,
      required: true,
    },
    isOrganizer: {
      type: Boolean,
      required: true,
    },
    tab: {
      type: String as PropType<AvailableTabs>,
      required: true,
    },
    identities: {
      type: Array as PropType<types.Identity[]>,
      required: true,
    },
    identitiesCsv: {
      type: Array as PropType<types.Identity[]>,
      required: true,
    },
    scoreboards: {
      type: Array as PropType<types.GroupScoreboard[]>,
      required: true,
    },
    userErrorRow: {
      type: String as PropType<null | string>,
      default: null,
    },
    searchResultUsers: {
      type: Array as PropType<types.ListItem[]>,
      required: true,
    },
    searchResultSchools: {
      type: Array as PropType<types.SchoolListItem[]>,
      required: true,
    },
    hasVisitedSection: {
      type: Boolean,
      required: true,
    },
  },
  emits: [
    'update:tab',
    'update-group',
    'update-search-result-schools',
    'update-search-result-users',
    'add-member',
    'edit-identity',
    'edit-identity-member',
    'change-password-identity',
    'change-password-identity-member',
    'remove',
    'cancel',
    'create-scoreboard',
    'bulk-identities',
    'download-identities',
    'read-csv',
    'invalid-file',
  ],
  data() {
    return {
      T,
      ui,
      AvailableTabs,
      selectedTab: this.tab as AvailableTabs,
      currentIdentities: this.identities as types.Identity[],
      currentIdentitiesCsv: this.identitiesCsv as types.Identity[],
      currentScoreboards: this.scoreboards as types.GroupScoreboard[],
    };
  },
  watch: {
    tab(newValue: AvailableTabs): void {
      if (!Object.values(AvailableTabs).includes(newValue)) {
        this.selectedTab = AvailableTabs.Members;
        return;
      }
      this.selectedTab = newValue;
    },
    selectedTab(newValue: AvailableTabs): void {
      this.$emit('update:tab', newValue);
    },
    identities(newValue: types.Identity[]): void {
      this.currentIdentities = newValue;
    },
    identitiesCsv(newValue: types.Identity[]): void {
      this.currentIdentitiesCsv = newValue;
    },
    scoreboards(newValue: types.GroupScoreboard[]): void {
      this.currentScoreboards = newValue;
    },
  },
});
</script>

<style scoped lang="scss">
@media (max-width: 576px) {
  h2 {
    text-align: center;
  }
}
</style>
