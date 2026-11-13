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
import { defineComponent } from 'vue';
import type { PropType } from 'vue';
import user_SidebarMainInfo from './SidebarMainInfo.vue';
import type { types } from '../../api_types';

export default defineComponent({
  name: 'ProfileWrapper',
  components: {
    'omegaup-user-maininfo': user_SidebarMainInfo,
  },
  props: {
    data: {
      type: Object as PropType<types.ExtraProfileDetails | null>,
      default: null,
    },
    profile: {
      type: Object as PropType<types.UserProfileInfo>,
      required: true,
    },
    selectedTab: {
      type: String as PropType<null | string>,
      default: null,
    },
    hasPassword: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['update:selectedTab'],
  data(): {
    currentSelectedTab: null | string;
  } {
    return {
      currentSelectedTab: this.selectedTab,
    };
  },
  watch: {
    currentSelectedTab(newValue: null | string): void {
      this.$emit('update:selectedTab', newValue);
    },
    selectedTab(newValue: null | string): void {
      this.currentSelectedTab = newValue;
    },
  },
});
</script>
