<template>
  <div data-user-profile-edit>
    <omegaup-user-profile-wrapper
      :profile="profile"
      :data="data"
      :selected-tab.sync="currentSelectedTab"
    >
      <template #title>
        <h3>{{ currentTitle }}</h3>
      </template>
      <template #content>
        <template v-if="currentSelectedTab === 'manage-identities'">
          <omegaup-user-manage-identities
            :identities="identities"
            @add-identity="(request) => $emit('add-identity', request)"
          ></omegaup-user-manage-identities>
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
import T from '../../lang';
import { types } from '../../api_types';
import user_ProfileWrapper from './ProfileWrapper.vue';
import { urlMapping } from './SidebarMainInfo.vue';
import user_ManageIdentities from './ManageIdentitiesv2.vue';

@Component({
  components: {
    'omegaup-user-profile-wrapper': user_ProfileWrapper,
    'omegaup-user-manage-identities': user_ManageIdentities,
  },
})
export default class Profile extends Vue {
  @Prop({ default: null }) data!: types.ExtraProfileDetails | null;
  @Prop() profile!: types.UserProfileInfo;
  @Prop({ default: 'see-profile' }) selectedTab!: string;
  @Prop() identities!: types.Identity[];

  T = T;
  currentSelectedTab = this.selectedTab;

  get currentTitle(): string {
    return (
      urlMapping.find((url) => url.key === this.currentSelectedTab)?.title ??
      'see-profile'
    );
  }
}
</script>
