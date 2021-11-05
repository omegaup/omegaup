<template>
  <div data-user-profile-edit>
    <omegaup-user-profile-wrapper
      :profile="profile"
      :data="data"
      :tab-selected="currentTabSelected"
      @update-tab="(tab) => (currentTabSelected = tab)"
    >
      <template #title>
        <span> Title for {{ currentTabSelected }} </span>
      </template>
      <template #content>
        <div>This is the content for {{ currentTabSelected }}</div>
      </template>
    </omegaup-user-profile-wrapper>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';
import user_ProfileWrapper from './ProfileWrapper.vue';

@Component({
  components: {
    'omegaup-user-profile-wrapper': user_ProfileWrapper,
  },
})
export default class UserProfile extends Vue {
  @Prop({ default: null }) data!: types.ExtraProfileDetails | null;
  @Prop() profile!: types.UserProfileInfo;
  @Prop({ default: null }) tabSelected!: null | string;

  T = T;
  currentTabSelected = this.tabSelected;
}
</script>
