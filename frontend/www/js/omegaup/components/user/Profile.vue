<template>
  <div data-user-profile-edit class="mx-auto">
    <omegaup-user-profile-wrapper
      :profile="profile"
      :data="data"
      :selected-tab.sync="currentSelectedTab"
      :has-password="hasPassword"
    >
      <template #message>
        <h1 v-if="!profile.is_own_profile && profile.is_private">
          {{ ui.info(T.userProfileIsPrivate) }}
        </h1>
      </template>
      <template #title>
        <h3 class="text-center mt-1">{{ currentTitle }}</h3>
      </template>
      <template #content>
        <template v-if="currentSelectedTab === 'view-profile'">
          <omegaup-user-view-profile
            :data="data"
            :profile="profile"
            :profile-badges="profileBadges"
            :visitor-badges="visitorBadges"
            :selected-tab.sync="currentViewProfileSelectedTab"
            :heatmap-data="heatmapData"
            :available-years="availableYears"
            :profile-statistics="profileStatistics"
            @heatmap-year-changed="
              (year) => $emit('heatmap-year-changed', year)
            "
          ></omegaup-user-view-profile>
        </template>
        <template v-else-if="currentSelectedTab === 'edit-basic-information'">
          <omegaup-user-edit-basic-information
            :data="data"
            :profile="profile"
            :countries="countries"
            @update-user-basic-information="
              (request) => $emit('update-user-basic-information', request)
            "
            @update-user-basic-information-error="
              (request) => $emit('update-user-basic-information-error', request)
            "
          ></omegaup-user-edit-basic-information>
        </template>
        <template v-else-if="currentSelectedTab === 'edit-preferences'">
          <omegaup-user-edit-preferences
            :profile="profile"
            @update-user-preferences="
              (request) => $emit('update-user-preferences', request)
            "
          ></omegaup-user-edit-preferences>
        </template>
        <template v-else-if="currentSelectedTab === 'manage-schools'">
          <omegaup-user-manage-schools
            :profile="profile"
            :search-result-schools="searchResultSchools"
            @update-search-result-schools="
              (query) => $emit('update-search-result-schools', query)
            "
            @update-user-schools="
              (request) => $emit('update-user-schools', request)
            "
          ></omegaup-user-manage-schools>
        </template>
        <template v-else-if="currentSelectedTab === 'manage-identities'">
          <omegaup-user-manage-identities
            :identities="identities"
            @add-identity="(request) => $emit('add-identity', request)"
          ></omegaup-user-manage-identities>
        </template>
        <template v-else-if="currentSelectedTab === 'manage-api-tokens'">
          <omegaup-user-manage-api-tokens
            :api-tokens="apiTokens"
            @create-api-token="(request) => $emit('create-api-token', request)"
            @revoke-api-token="(request) => $emit('revoke-api-token', request)"
          ></omegaup-user-manage-api-tokens>
        </template>
        <template v-else-if="currentSelectedTab === 'change-password'">
          <omegaup-user-edit-password
            @update-password="(request) => $emit('update-password', request)"
          ></omegaup-user-edit-password>
        </template>
        <template v-else-if="currentSelectedTab === 'add-password'">
          <omegaup-user-add-password
            :username="profile.username"
            @add-password="(request) => $emit('add-password', request)"
          ></omegaup-user-add-password>
        </template>
        <template v-else-if="currentSelectedTab === 'delete-account'">
          <omegaup-user-delete-account
            :username="profile.username"
            @request-delete-account="$emit('request-delete-account')"
          ></omegaup-user-delete-account>
        </template>
        <div v-else>
          {{ currentSelectedTab }}
        </div>
      </template>
    </omegaup-user-profile-wrapper>
  </div>
</template>

<script lang="ts">
import { Component, Prop, Vue, Watch } from 'vue-property-decorator';
import { dao, types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';
import user_BasicInformationEdit from './BasicInformationEdit.vue';
import userDeleteAccount from './DeleteAccount.vue';
import user_ManageApiTokens from './ManageApiTokens.vue';
import user_ManageIdentities from './ManageIdentities.vue';
import user_ManageSchools from './ManageSchools.vue';
import user_PasswordAdd from './PasswordAdd.vue';
import user_PasswordEdit from './PasswordEdit.vue';
import user_PreferencesEdit from './PreferencesEdit.vue';
import { DifficultyStats } from './ProblemSolvingProgress.vue';
import user_ProfileWrapper from './ProfileWrapper.vue';
import { urlMapping } from './SidebarMainInfo.vue';
import user_ViewProfile from './ViewProfile.vue';

export interface ProfileStatistics {
  solved: number;
  attempting: number;
  difficulty: DifficultyStats;
  tags: Array<{ name: string; count: number }>;
}

@Component({
  components: {
    'omegaup-user-profile-wrapper': user_ProfileWrapper,
    'omegaup-user-view-profile': user_ViewProfile,
    'omegaup-user-edit-preferences': user_PreferencesEdit,
    'omegaup-user-edit-basic-information': user_BasicInformationEdit,
    'omegaup-user-edit-password': user_PasswordEdit,
    'omegaup-user-add-password': user_PasswordAdd,
    'omegaup-user-manage-identities': user_ManageIdentities,
    'omegaup-user-manage-api-tokens': user_ManageApiTokens,
    'omegaup-user-manage-schools': user_ManageSchools,
    'omegaup-user-delete-account': userDeleteAccount,
  },
})
export default class Profile extends Vue {
  @Prop({ default: null }) data!: types.ExtraProfileDetails | null;
  @Prop() profile!: types.UserProfileInfo;
  @Prop({ default: 'view-profile' }) selectedTab!: string;
  @Prop({ default: null }) viewProfileSelectedTab!: string | null;
  @Prop() identities!: types.Identity[];
  @Prop() apiTokens!: types.ApiToken[];
  @Prop() profileBadges!: Set<string>;
  @Prop() visitorBadges!: Set<string>;
  @Prop() countries!: dao.Countries[];
  @Prop() programmingLanguages!: { [key: string]: string };
  @Prop() hasPassword!: boolean;
  @Prop() searchResultSchools!: types.SchoolListItem[];
  @Prop({ default: () => [] }) heatmapData!: Array<{
    date: string;
    count: number;
  }>;
  @Prop({ default: () => [] }) availableYears!: number[];
  @Prop({ default: null }) profileStatistics!: ProfileStatistics | null;

  T = T;
  ui = ui;
  currentSelectedTab = this.selectedTab;
  currentViewProfileSelectedTab = this.viewProfileSelectedTab;

  get currentTitle(): string {
    if (!this.profile.is_own_profile) {
      return T.omegaupTitleProfile;
    }
    return (
      urlMapping.find((url) => url.key === this.currentSelectedTab)?.title ??
      'view-profile'
    );
  }

  @Watch('selectedTab')
  onSelectedTabChanged(newValue: string) {
    this.currentSelectedTab = newValue;
  }
}
</script>

<style scoped>
[data-user-profile-edit] {
  max-width: 69rem;
  margin: 3rem 0;
}
</style>
