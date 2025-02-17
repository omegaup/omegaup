<template>
  <div class="container-fluid p-0 mt-0">
    <slot name="message"></slot>
    <div class="row">
      <div class="col-md-3 col-lg-3">
        <omegaup-user-maininfo
          :profile="profile"
          :data="data"
          :selected-tab.sync="currentSelectedTab"
          :has-password="hasPassword"
          :is-admin="isAdmin"
        >
        </omegaup-user-maininfo>
      </div>
      <div class="col-md-9 col-lg-9 sticky-top">
        <div class="card">
          <div class="card-header">
            <slot name="title"></slot>
          </div>
          <div class="card-body">
            <slot name="content"></slot>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import user_SidebarMainInfo from './SidebarMainInfo.vue';
import { types } from '../../api_types';

@Component({
  components: {
    'omegaup-user-maininfo': user_SidebarMainInfo,
  },
})
export default class ProfileWrapper extends Vue {
  @Prop({ default: null }) data!: types.ExtraProfileDetails | null;
  @Prop() profile!: types.UserProfileInfo;
  @Prop({ default: null }) selectedTab!: null | string;
  @Prop() hasPassword!: boolean;
  @Prop() isAdmin!: boolean;

  currentSelectedTab = this.selectedTab;

  @Watch('currentSelectedTab')
  onCurrentSelectedTabChanged(newValue: string) {
    this.$emit('update:selectedTab', newValue);
  }
}
</script>
