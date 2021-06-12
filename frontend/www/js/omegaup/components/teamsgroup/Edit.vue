<template>
  <div data-teams-group-edit>
    <div class="page-header">
      <h2>
        {{
          ui.formatString(T.teamsGroupEditTitleWithName, {
            name: teamsGroupName,
          })
        }}
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
        <omegaup-group-form
          :is-update="true"
          :group-name="teamsGroupName"
          :group-alias="teamsGroupAlias"
          :group-description="teamsGroupDescription"
          @update-group="
            (name, description) =>
              $emit('update-teams-group', name, description)
          "
        ></omegaup-group-form>
      </div>

      <div
        v-if="selectedTab === AvailableTabs.Teams"
        class="tab-pane active"
        role="tabpanel"
      >
        <omegaup-teams-group-teams
          :teams="currentTeamsIdentities"
          :teams-group-alias="teamsGroupAlias"
          :countries="countries"
          @edit-identity-team="
            (request) => $emit('edit-identity-team', request)
          "
          @change-password-identity-team="
            (request) => $emit('change-password-identity-team', request)
          "
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
          :teams-group-alias="teamsGroupAlias"
          :team-error-row="teamErrorRow"
          :search-result-users="searchResultUsers"
          @bulk-identities="
            (identities) => $emit('bulk-identities', identities)
          "
          @download-teams="(teams) => $emit('download-teams', teams)"
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
import group_Form from '../group/Form.vue';
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
    'omegaup-group-form': group_Form,
    'omegaup-teams-group-upload': teamsgroup_Upload,
    'omegaup-teams-group-teams': teamsgroup_Teams,
  },
})
export default class TeamsGroupEdit extends Vue {
  @Prop() teamsGroupAlias!: string;
  @Prop() teamsGroupName!: string;
  @Prop() teamsGroupDescription!: string;
  @Prop() countries!: dao.Countries[];
  @Prop() isOrganizer!: boolean;
  @Prop() tab!: AvailableTabs;
  @Prop({ default: () => [] }) teamsIdentities!: types.Identity[];
  @Prop({ default: () => [] }) teamsIdentitiesCsv!: types.Identity[];
  @Prop() teamErrorRow!: null | string;
  @Prop() searchResultUsers!: types.ListItem[];

  T = T;
  ui = ui;
  AvailableTabs = AvailableTabs;
  selectedTab: AvailableTabs = this.tab;
  currentTeamsIdentities = this.teamsIdentities;
  currentTeamsIdentitiesCsv = this.teamsIdentitiesCsv;

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

  @Watch('teamsIdentitiesCsv')
  onTeamsIdentitiesCsvChanged(newValue: types.Identity[]): void {
    this.currentTeamsIdentitiesCsv = newValue;
  }
}
</script>
