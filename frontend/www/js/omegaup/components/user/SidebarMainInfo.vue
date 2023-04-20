<template>
  <div class="card">
    <div class="card-header">
      <omegaup-countryflag
        v-if="profile.country_id"
        class="m-1"
        :country="profile.country_id"
      />
      <div class="text-center rounded-circle bottom-margin">
        <img class="rounded-circle" :src="profile.gravatar_92" />
      </div>

      <div class="mb-3 text-center">
        <omegaup-user-username
          :classname="profile.classname"
          :username="profile.username"
        ></omegaup-user-username>
      </div>
      <div class="mb-3 text-center">
        <h4 v-if="profile.rankinfo.rank > 0" class="m-0">
          {{ `#${profile.rankinfo.rank}` }}
        </h4>
        <small v-else>
          <strong> {{ rank }} </strong>
        </small>
        <p>
          <small>
            {{ T.profileRank }}
          </small>
        </p>
      </div>
      <div
        v-if="profile.is_own_profile || !profile.is_private"
        class="mb-3 text-center"
        data-solved-problems
      >
        <h4 class="m-0">
          {{ Object.keys(solvedProblems).length }}
        </h4>
        <p>
          <small>{{ T.profileSolvedProblems }}</small>
        </p>
      </div>
      <div
        v-if="
          profile.preferred_language &&
          (profile.is_own_profile || !profile.is_private)
        "
        class="mb-3 text-center"
      >
        <h5 class="m-0">
          {{
            profile.programming_languages[profile.preferred_language].split(
              ' ',
            )[0]
          }}
        </h5>
        <p>
          <small>{{ T.userEditPreferredProgrammingLanguage }}</small>
        </p>
      </div>
    </div>
    <div v-if="profile.is_own_profile" class="card-body text-center">
      <a
        v-for="url in currentUrlMapping.filter((url) => url.visible)"
        :key="url.key"
        class="btn btn-primary btn-sm my-1 w-100"
        :href="`/profile/#${url.key}`"
        :class="{ disabled: url.key === selectedTab }"
        @click="$emit('update:selectedTab', url.key)"
      >
        {{ url.title }}
      </a>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import T from '../../lang';
import country_Flag from '../CountryFlag.vue';
import user_Username from './Username.vue';
import { types } from '../../api_types';
import { Problem } from '../../linkable_resource';

export const urlMapping: { key: string; title: string; visible: boolean }[] = [
  { key: 'view-profile', title: T.userEditViewProfile, visible: true },
  {
    key: 'edit-basic-information',
    title: T.profileBasicInformation,
    visible: true,
  },
  { key: 'edit-preferences', title: T.userEditPreferences, visible: true },
  { key: 'manage-schools', title: T.userEditManageSchools, visible: true },
  { key: 'manage-identities', title: T.profileManageIdentities, visible: true },
  { key: 'change-password', title: T.userEditChangePassword, visible: false },
  { key: 'add-password', title: T.userEditAddPassword, visible: false },
  { key: 'change-email', title: T.userEditChangeEmail, visible: false },
  { key: 'delete-account', title: T.userEditDeleteAccount, visible: true },
];

@Component({
  components: {
    'omegaup-countryflag': country_Flag,
    'omegaup-user-username': user_Username,
  },
})
export default class UserSidebarMainInfo extends Vue {
  @Prop({ default: null }) data!: types.ExtraProfileDetails | null;
  @Prop() profile!: types.UserProfileInfo;
  @Prop() selectedTab!: string;
  @Prop() hasPassword!: boolean;

  T = T;
  urlMapping = urlMapping;
  currentSelectedTab = this.getSelectedValidTab(
    this.selectedTab,
    this.currentUrlMapping,
  );

  get solvedProblems(): Problem[] {
    if (!this.data?.solvedProblems) return [];
    return this.data.solvedProblems.map((problem) => new Problem(problem));
  }
  get rank(): string {
    switch (this.profile.classname) {
      case 'user-rank-beginner':
        return T.profileRankBeginner;
      case 'user-rank-specialist':
        return T.profileRankSpecialist;
      case 'user-rank-expert':
        return T.profileRankExpert;
      case 'user-rank-master':
        return T.profileRankMaster;
      case 'user-rank-international-master':
        return T.profileRankInternationalMaster;
      default:
        return T.profileRankUnrated;
    }
  }

  get currentUrlMapping(): {
    key: string;
    title: string;
    visible: boolean;
  }[] {
    if (!this.profile.is_own_profile) {
      return [];
    }
    const changePasswordRowIndex = urlMapping.findIndex(
      (url) => url.key === 'change-password',
    );
    const addPasswordRowIndex = urlMapping.findIndex(
      (url) => url.key === 'add-password',
    );
    if (!changePasswordRowIndex || !addPasswordRowIndex) {
      return urlMapping;
    }
    if (this.hasPassword) {
      urlMapping[changePasswordRowIndex].visible = true;
      urlMapping[addPasswordRowIndex].visible = false;
      return urlMapping;
    }
    urlMapping[addPasswordRowIndex].visible = true;
    urlMapping[changePasswordRowIndex].visible = false;
    return urlMapping;
  }

  getSelectedValidTab(
    tab: string,
    urls: { key: string; title: string; visible: boolean }[],
  ): string {
    const validTabs = urls.filter((url) => url.visible).map((url) => url.key);
    const isValidTab = validTabs.includes(tab);
    if (!isValidTab) {
      this.$emit('update:selectedTab', 'view-profile');
      return 'view-profile';
    }
    return tab;
  }

  @Watch('selectedTab')
  onSelectedTabChange(newValue: string) {
    const validTab = this.getSelectedValidTab(newValue, this.currentUrlMapping);
    this.currentSelectedTab = validTab;
    if (validTab !== newValue) {
      this.$emit('update:selectedTab', validTab);
    }
  }
}
</script>
