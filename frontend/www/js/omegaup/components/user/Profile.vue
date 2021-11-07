<template>
  <div data-user-profile-edit>
    <omegaup-user-profile-wrapper
      :profile="profile"
      :data="data"
      :tab-selected="currentTabSelected"
      :url-mapping="urlMapping"
      @update-tab="(tab) => (currentTabSelected = tab)"
    >
      <template #title>
        <h3>{{ currentTitle }}</h3>
      </template>
      <template #content>
        <template v-if="currentTabSelected === 'manage-identities'">
          <omegaup-user-manage-identities
            :identities="identities"
            @add-identity="(request) => $emit('add-identity', request)"
          ></omegaup-user-manage-identities>
        </template>
        <div v-else>
          {{ currentTabSelected }}
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
import user_ManageIdentities from './ManageIdentitiesv2.vue';

@Component({
  components: {
    'omegaup-user-profile-wrapper': user_ProfileWrapper,
    'omegaup-user-manage-identities': user_ManageIdentities,
  },
})
export default class UserProfile extends Vue {
  @Prop({ default: null }) data!: types.ExtraProfileDetails | null;
  @Prop() profile!: types.UserProfileInfo;
  @Prop({ default: 'see-profile' }) tabSelected!: string;
  @Prop({ default: () => [] }) urlMapping!: {
    key: string;
    title: string;
    visible: boolean;
  }[];
  @Prop() identities!: types.Identity[];

  T = T;
  currentTabSelected = this.tabSelected;

  get currentTitle(): string {
    return (
      this.urlMapping.find((url) => url.key === this.currentTabSelected)
        ?.title ?? 'see-profile'
    );
  }
}
</script>
