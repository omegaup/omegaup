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
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
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

@Component({
  components: {
    'omegaup-group-create-identities': group_Identities,
    'omegaup-group-form': group_Form,
    'omegaup-group-members': group_Members,
    'omegaup-group-scoreboards': group_Scoreboards,
  },
})
export default class GroupEdit extends Vue {
  @Prop() groupAlias!: string;
  @Prop() groupDescription!: string;
  @Prop() groupName!: string;
  @Prop() countries!: dao.Countries[];
  @Prop() isOrganizer!: boolean;
  @Prop() tab!: AvailableTabs;
  @Prop() identities!: types.Identity[];
  @Prop() identitiesCsv!: types.Identity[];
  @Prop() scoreboards!: types.GroupScoreboard[];
  @Prop() userErrorRow!: null | string;
  @Prop() searchResultUsers!: types.ListItem[];
  @Prop() searchResultSchools!: types.SchoolListItem[];
  @Prop() hasVisitedSection!: boolean;

  T = T;
  ui = ui;
  AvailableTabs = AvailableTabs;
  selectedTab: AvailableTabs = this.tab;
  currentIdentities = this.identities;
  currentIdentitiesCsv = this.identitiesCsv;
  currentScoreboards = this.scoreboards;

  @Watch('tab')
  onInitialTabChanged(newValue: AvailableTabs): void {
    if (!Object.values(AvailableTabs).includes(this.tab)) {
      this.selectedTab = AvailableTabs.Members;
      return;
    }
    this.selectedTab = newValue;
  }

  @Watch('identities')
  onInitialIdentitiesChanged(newValue: types.Identity[]): void {
    this.currentIdentities = newValue;
  }

  @Watch('identitiesCsv')
  onInitialIdentitiesCsvChanged(newValue: types.Identity[]): void {
    this.currentIdentitiesCsv = newValue;
  }

  @Watch('scoreboards')
  onInitialScoreboardsChanged(newValue: types.GroupScoreboard[]): void {
    this.currentScoreboards = newValue;
  }
}
</script>

<style scoped lang="scss">
@media (max-width: 576px) {
  h2 {
    text-align: center;
  }
}
</style>
