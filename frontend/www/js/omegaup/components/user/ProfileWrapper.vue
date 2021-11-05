<template>
  <div class="container-fluid p-0 mt-0">
    <slot name="message"></slot>
    <div class="row">
      <div class="col-md-3">
        <omegaup-user-maininfo
          :profile="profile"
          :data="data"
          :tab-selected.sync="currentTabSelected"
        >
        </omegaup-user-maininfo>
      </div>
      <div class="col-md-9 sticky-top">
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
import user_NavbarMainInfo from './NavbarMainInfo.vue';
import { types } from '../../api_types';

@Component({
  components: {
    'omegaup-user-maininfo': user_NavbarMainInfo,
  },
})
export default class UserProfile extends Vue {
  @Prop({ default: null }) data!: types.ExtraProfileDetails | null;
  @Prop() profile!: types.UserProfileInfo;
  @Prop({ default: null }) tabSelected!: null | string;

  currentTabSelected = this.tabSelected;

  @Watch('currentTabSelected')
  onCurrentTabSelectedChanged(newValue: string) {
    this.$emit('update-tab', newValue);
  }
}
</script>
