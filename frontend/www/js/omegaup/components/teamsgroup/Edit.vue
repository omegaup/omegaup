<template>
  <div data-teams-group-edit>
    <div class="page-header">
      <h2>
        {{ ui.formatString(T.teamsGroupEditTitleWithName, { name }) }}
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
          :href="`#${AvailableTabs.Teams}`"
          class="nav-link"
          data-tab-teams
          :class="{ active: selectedTab === AvailableTabs.Teams }"
          @click="selectedTab = AvailableTabs.Teams"
          >{{ T.teamsGroupEditTeams }}</a
        >
      </li>
      <li class="nav-item" role="presentation">
        <a
          :href="`#${AvailableTabs.Upload}`"
          class="nav-link"
          data-tab-identities
          :class="{ active: selectedTab === AvailableTabs.Upload }"
          @click="selectedTab = AvailableTabs.Upload"
          >{{ T.teamsGroupUploadIdentitiesAsTeams }}</a
        >
      </li>
    </ul>

    <div class="tab-content">
      <div
        v-if="selectedTab === AvailableTabs.Edit"
        class="tab-pane active"
        role="tabpanel"
      >
        <omegaup-teams-group-form
          :name="name"
          :alias="alias"
          :description="description"
          :number-of-contestants="numberOfContestants"
          @update-teams-group="
            (request) => $emit('update-teams-group', request)
          "
        ></omegaup-teams-group-form>
      </div>

      <div
        v-if="selectedTab === AvailableTabs.Teams"
        class="tab-pane active"
        role="tabpanel"
      >
        <omegaup-teams-group-teams
          :teams="currentTeamsIdentities"
          :alias="alias"
          :countries="countries"
          :search-result-users="searchResultUsers"
          :search-result-schools="searchResultSchools"
          :teams-members="teamsMembers"
          @update-identity-team="
            (identity) => $emit('update-identity-team', identity)
          "
          @update-search-result-users="
            (query) => $emit('update-search-result-users', query)
          "
          @update-search-result-schools="
            (query) => $emit('update-search-result-schools', query)
          "
          @edit-identity-team="
            (request) => $emit('edit-identity-team', request)
          "
          @change-password-identity-team="
            (request) => $emit('change-password-identity-team', request)
          "
          @change-password-identity="
            (request) => $emit('change-password-identity', request)
          "
          @add-members="(request) => $emit('add-members', request)"
          @remove-member="(request) => $emit('remove-member', request)"
          @remove="(name) => $emit('remove', name)"
          @cancel="(teamComponent) => $emit('cancel', teamComponent)"
        ></omegaup-teams-group-teams>
      </div>

      <div
        v-if="selectedTab === AvailableTabs.Upload"
        class="tab-pane active"
        role="tabpanel"
      >
        <omegaup-teams-group-upload
          :team-error-row="teamErrorRow"
          :search-result-users="searchResultUsers"
          :number-of-contestants="numberOfContestants"
          :is-loading.sync="isLoading"
          @bulk-identities="
            (identities) => $emit('bulk-identities', identities)
          "
          @download-teams="(identities) => $emit('download-teams', identities)"
          @read-csv="(source) => $emit('read-csv', source)"
          @invalid-file="$emit('invalid-file')"
          @update-search-result-users="
            (query) => $emit('update-search-result-users', query)
          "
        ></omegaup-teams-group-upload>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import teamsgroup_FormUpdate from './FormUpdate.vue';
import teamsgroup_Upload from './Upload.vue';
import teamsgroup_Teams from './Teams.vue';
import T from '../../lang';
import { dao, types } from '../../api_types';
import * as ui from '../../ui';

export enum AvailableTabs {
  Edit = 'edit',
  Teams = 'teams',
  Upload = 'upload',
}

@Component({
  components: {
    'omegaup-teams-group-form': teamsgroup_FormUpdate,
    'omegaup-teams-group-upload': teamsgroup_Upload,
    'omegaup-teams-group-teams': teamsgroup_Teams,
  },
})
export default class TeamsGroupEdit extends Vue {
  @Prop() alias!: string;
  @Prop() name!: string;
  @Prop() description!: string;
  @Prop() numberOfContestants!: number;
  @Prop() countries!: dao.Countries[];
  @Prop() isOrganizer!: boolean;
  @Prop() tab!: AvailableTabs;
  @Prop({ default: () => [] }) teamsIdentities!: types.Identity[];
  @Prop({ default: () => [] }) teamsMembers!: types.TeamMember[];
  @Prop() teamErrorRow!: null | string;
  @Prop() searchResultUsers!: types.ListItem[];
  @Prop() searchResultSchools!: types.SchoolListItem[];
  @Prop() isLoading!: boolean;

  T = T;
  ui = ui;
  AvailableTabs = AvailableTabs;
  selectedTab: AvailableTabs = this.tab;
  currentTeamsIdentities = this.teamsIdentities;

  @Watch('tab')
  onTabChanged(newValue: AvailableTabs): void {
    if (!Object.values(AvailableTabs).includes(this.tab)) {
      this.selectedTab = AvailableTabs.Teams;
      return;
    }
    this.selectedTab = newValue;
  }

  @Watch('teamsIdentities')
  onTeamsIdentitiesChanged(newValue: types.Identity[]): void {
    this.currentTeamsIdentities = newValue;
  }
}
</script>
