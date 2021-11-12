<template>
  <div data-user-profile-edit class="m-5">
    <omegaup-user-profile-wrapper
      :profile="profile"
      :data="data"
      :selected-tab.sync="currentSelectedTab"
    >
      <template #message>
        <h1 v-if="!profile.is_own_profile && profile.is_private">
          {{ ui.info(T.userProfileIsPrivate) }}
        </h1>
      </template>
      <template #title>
        <h3>{{ currentTitle }}</h3>
      </template>
      <template #content>
        <template v-if="currentSelectedTab === 'see-profile'">
          <omegaup-user-see-profile
            :data="data"
            :profile="profile"
            :profile-badges="profileBadges"
            :visitor-badges="visitorBadges"
          ></omegaup-user-see-profile>
        </template>
        <template v-else-if="currentSelectedTab === 'edit-basic-information'">
          <omegaup-user-edit-basic-information
            :data="data"
            :profile="profile"
            :countries="countries"
            @update-user-basic-information="
              (request) => $emit('update-user-basic-information', request)
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
        <template v-else-if="currentSelectedTab === 'change-password'">
          <omegaup-user-edit-password></omegaup-user-edit-password>
        </template>
        <div v-else>
          {{ currentSelectedTab }}
        </div>
      </template>
    </omegaup-user-profile-wrapper>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import * as ui from '../../ui';
import T from '../../lang';
import { dao, types } from '../../api_types';
import user_ProfileWrapper from './ProfileWrapper.vue';
import user_SeeProfile from './Profilev2.vue';
import user_PreferencesEdit from './PreferencesEdit.vue';
import user_BasicInformationEdit from './BasicInformationEdit.vue';
import user_PasswordEdit from './PasswordEdit.vue';
import { urlMapping } from './SidebarMainInfo.vue';
import user_ManageIdentities from './ManageIdentitiesv2.vue';
import user_ManageSchools from './ManageSchools.vue';

@Component({
  components: {
    'omegaup-user-profile-wrapper': user_ProfileWrapper,
    'omegaup-user-see-profile': user_SeeProfile,
    'omegaup-user-edit-preferences': user_PreferencesEdit,
    'omegaup-user-edit-basic-information': user_BasicInformationEdit,
    'omegaup-user-edit-password': user_PasswordEdit,
    'omegaup-user-manage-identities': user_ManageIdentities,
    'omegaup-user-manage-schools': user_ManageSchools,
  },
})
export default class Profile extends Vue {
  @Prop({ default: null }) data!: types.ExtraProfileDetails | null;
  @Prop() profile!: types.UserProfileInfo;
  @Prop({ default: 'see-profile' }) selectedTab!: string;
  @Prop() identities!: types.Identity[];
  @Prop() profileBadges!: Set<string>;
  @Prop() visitorBadges!: Set<string>;
  @Prop() countries!: dao.Countries[];
  @Prop() programmingLanguages!: { [key: string]: string };

  T = T;
  ui = ui;
  currentSelectedTab = this.selectedTab;

  get currentTitle(): string {
    if (!this.profile.is_own_profile) {
      return T.omegaupTitleProfile;
    }
    return (
      urlMapping.find((url) => url.key === this.currentSelectedTab)?.title ??
      'see-profile'
    );
  }
}
</script>
