<template>
  <div class="group-edit">
    <div class="page-header">
      <h1>
        <span>
          {{ ui.formatString(T.omegaupTitleGroupsEdit, { name: groupName }) }}
        </span>
      </h1>
    </div>
    <ul class="nav nav-pills">
      <li class="nav-item" role="presentation">
        <a
          href="#"
          class="nav-link"
          data-tab-members
          :class="{ active: showTab === 'members' }"
          @click="showTab = 'members'"
          >{{ T.groupEditMembers }}</a
        >
      </li>
      <li class="nav-item" role="presentation">
        <a
          href="#"
          class="nav-link"
          data-tab-scoreboards
          :class="{ active: showTab === 'scoreboards' }"
          @click="showTab = 'scoreboards'"
          >{{ T.groupEditScoreboards }}</a
        >
      </li>
      <li class="nav-item" role="presentation">
        <a
          href="#"
          class="nav-link"
          data-tab-identities
          :class="{ active: showTab === 'identities' }"
          @click="showTab = 'identities'"
          >{{ T.groupCreateIdentities }}</a
        >
      </li>
    </ul>

    <div class="tab-content">
      <div v-if="showTab === 'members'" class="tab-pane active" role="tabpanel">
        <omegaup-group-members
          :identities="identities"
          :identities-csv="identitiesCsv"
          :group-alias="groupAlias"
          :countries="countries"
          @add-member="
            (memberComponent, username) =>
              $emit('add-member', memberComponent, username)
          "
          @edit-identity="
            (memberComponent, identity) =>
              $emit('edit-identity', memberComponent, identity)
          "
          @edit-identity-member="
            (
              memberComponent,
              originalUsername,
              username,
              name,
              country,
              state,
              school,
              schoolId,
            ) =>
              $emit(
                'edit-identity-member',
                memberComponent,
                originalUsername,
                username,
                name,
                country,
                state,
                school,
                schoolId,
              )
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
        ></omegaup-group-members>
      </div>

      <div
        v-if="showTab === 'scoreboards'"
        class="tab-pane active"
        role="tabpanel"
      >
        <omegaup-group-scoreboards
          :group-alias="groupAlias"
          :scoreboards.sync="scoreboards"
          @create-scoreboard="
            (title, alias, description) =>
              $emit('create-scoreboard', title, alias, description)
          "
        ></omegaup-group-scoreboards>
      </div>

      <div
        v-if="showTab === 'identities'"
        class="tab-pane active"
        role="tabpanel"
      >
        <omegaup-group-create-identities
          :group-alias="groupAlias"
          :user-error-row="userErrorRow"
          @bulk-identities="
            (identitiesComponent, identities) =>
              $emit('bulk-identities', identitiesComponent, identities)
          "
          @download-identities="
            (identities) => $emit('download-identities', identities)
          "
          @read-csv="
            (identitiesComponent, fileUpload) =>
              $emit('read-csv', identitiesComponent, fileUpload)
          "
        ></omegaup-group-create-identities>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import group_Identities from './Identities.vue';
import group_Members from './Members.vue';
import group_Scoreboards from './Scoreboards.vue';
import T from '../../lang';
import { dao, types } from '../../api_types';
import * as ui from '../../ui';

const availableTabs = ['memebers', 'scoreboards', 'identities'];

@Component({
  components: {
    'omegaup-group-create-identities': group_Identities,
    'omegaup-group-members': group_Members,
    'omegaup-group-scoreboards': group_Scoreboards,
  },
})
export default class GroupEdit extends Vue {
  @Prop() groupAlias!: string;
  @Prop() groupName!: string;
  @Prop() countries!: dao.Countries[];
  @Prop() isOrganizer!: boolean;
  @Prop() initialTab!: string;
  @Prop() initialIdentities!: types.Identity[];
  @Prop() initialIdentitiesCsv!: types.Identity[];
  @Prop() initialScoreboards!: types.GroupScoreboard[];

  T = T;
  ui = ui;
  showTab = this.initialTab;
  userErrorRow = null;
  identities = this.initialIdentities;
  identitiesCsv = this.initialIdentitiesCsv;
  scoreboards = this.initialScoreboards;

  @Watch('initialTab')
  onInitialTabChanged(newValue: string): void {
    if (!availableTabs.includes(this.initialTab)) {
      this.showTab = 'members';
      return;
    }
    this.showTab = newValue;
  }

  @Watch('initialIdentities')
  onInitialIdentitiesChanged(newValue: types.Identity[]): void {
    this.identities = newValue;
  }

  @Watch('initialIdentitiesCsv')
  onInitialIdentitiesCsvChanged(newValue: types.Identity[]): void {
    this.identitiesCsv = newValue;
  }

  @Watch('initialScoreboards')
  onInitialScoreboardsChanged(newValue: types.GroupScoreboard[]): void {
    this.scoreboards = newValue;
  }
}
</script>
