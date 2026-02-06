<template>
  <div class="card">
    <div class="card-header">
      <div class="text-center rounded-circle bottom-margin mt-3">
        <div v-if="profile.is_own_profile" class="profile-picture-container">
          <img
            class="rounded-circle profile-picture"
            :src="profile.gravatar_92"
            @click="redirectToGravatar"
          />
          <div
            class="profile-edit-overlay"
            :title="T.userEditProfileImage"
            @click="redirectToGravatar"
          >
            <div class="edit-icon">
              <svg
                width="20"
                height="20"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"
                  fill="white"
                />
              </svg>
            </div>
          </div>
        </div>
        <img v-else class="rounded-circle" :src="profile.gravatar_92" />
      </div>
      <div class="mb-3 text-center mt-2">
        <omegaup-countryflag
          v-if="profile.country_id"
          :country="profile.country_id"
        />
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
        <h4 v-if="profile.rankinfo.author_ranking > 0" class="m-0">
          {{ `#${profile.rankinfo.author_ranking}` }}
        </h4>
        <small v-else>
          <strong> {{ T.authorRankUnranked }} </strong>
        </small>
        <p>
          <small>
            {{ T.profileAuthorRank }}
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
        data-preferred-programming-languages
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
  { key: 'manage-api-tokens', title: T.profileManageApiTokens, visible: true },
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

  redirectToGravatar(): void {
    window.open('https://www.gravatar.com', '_blank');
  }
}
</script>

<style scoped>
.profile-picture-container {
  position: relative;
  display: inline-block;
  cursor: pointer;
}

.profile-picture {
  width: 92px;
  height: 92px;
  object-fit: cover;
  transition: opacity 0.2s ease-in-out;
}

.profile-edit-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 92px;
  height: 92px;
  background-color: rgba(0, 0, 0, 0.6);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.2s ease-in-out;
}

.profile-picture-container:hover .profile-edit-overlay {
  opacity: 1;
}

.profile-picture-container:hover .profile-picture {
  opacity: 0.8;
}

.edit-icon {
  color: white;
  font-size: 16px;
  z-index: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

.edit-icon svg {
  width: 20px;
  height: 20px;
  filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.5));
}
</style>
