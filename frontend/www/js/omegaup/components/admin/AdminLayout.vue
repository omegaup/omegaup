<template>
  <div class="admin-dashboard-layout row no-gutters">
    <nav
      class="admin-dashboard-sidebar col-auto"
      :class="{ 'admin-dashboard-sidebar--expanded': expanded }"
    >
      <button
        type="button"
        class="admin-dashboard-sidebar__toggle btn btn-link"
        :aria-label="T.omegaupOperationsDashboardToggleSidebar"
        :aria-expanded="expanded.toString()"
        @click="expanded = !expanded"
      >
        <BIconList v-if="!expanded" scale="1.4" />
        <BIconChevronDoubleLeft v-else scale="1.2" />
      </button>

      <hr class="admin-dashboard-sidebar__divider" />

      <a
        v-for="item in sidebarItems"
        :key="item.href"
        :href="item.href"
        class="admin-dashboard-sidebar__item"
        :title="item.label"
      >
        <component
          :is="item.icon"
          scale="1.3"
          class="admin-dashboard-sidebar__icon"
        />
        <span v-if="expanded" class="admin-dashboard-sidebar__label">{{
          item.label
        }}</span>
      </a>
    </nav>

    <div class="admin-dashboard-content col">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb admin-dashboard-breadcrumb">
          <li class="breadcrumb-item">
            {{ T.omegaupTitleAdminOperations }}
          </li>
          <li v-if="currentModule" class="breadcrumb-item active">
            {{ currentModule }}
          </li>
        </ol>
      </nav>
      <slot />
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import {
  BIconList,
  BIconChevronDoubleLeft,
  BIconPersonLinesFill,
  BIconClockHistory,
  BIconHeadset,
  BIconGearFill,
} from 'bootstrap-vue';
import T from '../../lang';

interface SidebarItem {
  href: string;
  label: string;
  icon: string;
}

@Component({
  components: {
    BIconList,
    BIconChevronDoubleLeft,
    BIconPersonLinesFill,
    BIconClockHistory,
    BIconHeadset,
    BIconGearFill,
  },
})
export default class AdminLayout extends Vue {
  @Prop({ default: '' }) currentModule!: string;

  T = T;
  expanded = false;

  get sidebarItems(): SidebarItem[] {
    return [
      {
        href: '/admin/user/',
        label: T.omegaupTitleAdminUsers,
        icon: 'BIconPersonLinesFill',
      },
      {
        href: '/admin/crons/',
        label: T.omegaupTitleAdminCrons,
        icon: 'BIconClockHistory',
      },
      {
        href: '/admin/support/',
        label: T.omegaupTitleSupportDashboard,
        icon: 'BIconHeadset',
      },
      {
        href: '/admin/settings/',
        label: T.omegaupTitleAdminSettings,
        icon: 'BIconGearFill',
      },
    ];
  }
}
</script>

<style lang="scss" scoped>
.admin-dashboard-layout {
  min-height: calc(100vh - 12rem);
}

.admin-dashboard-sidebar {
  display: flex;
  flex-direction: column;
  align-items: stretch;
  width: 4.5rem;
  flex-shrink: 0;
  padding: 1.25rem 0;
  background-color: #f8f9fa;
  border-right: 1px solid #dee2e6;
  transition: width 0.2s ease-in-out;
  overflow: hidden;

  &--expanded {
    width: 15rem;
  }
}

.admin-dashboard-sidebar__toggle {
  display: flex;
  align-items: center;
  padding: 0.5rem 1.35rem;
  color: #495057;

  &:hover,
  &:focus {
    color: #0d6efd;
    text-decoration: none;
  }
}

.admin-dashboard-sidebar__divider {
  margin: 0.5rem 0 1rem;
  border-top: 1px solid #dee2e6;
}

.admin-dashboard-sidebar__item {
  display: flex;
  align-items: center;
  width: 100%;
  padding: 0.85rem 1.35rem;
  color: #495057;
  text-decoration: none;
  white-space: nowrap;

  &:hover {
    background-color: #e9ecef;
    color: #0d6efd;
    text-decoration: none;
  }
}

.admin-dashboard-sidebar__icon {
  flex-shrink: 0;
}

.admin-dashboard-sidebar__label {
  margin-left: 1rem;
  font-size: 0.95rem;
}

.admin-dashboard-content {
  padding: 1.5rem 2rem;
}

.admin-dashboard-breadcrumb {
  background: transparent;
  padding: 0;
  margin-bottom: 1.25rem;
}
</style>
