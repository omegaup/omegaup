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
        <span class="admin-dashboard-sidebar__icon">
          <BIconList v-if="!expanded" scale="1.3" />
          <BIconChevronDoubleLeft v-else scale="1.3" />
        </span>
      </button>

      <hr class="admin-dashboard-sidebar__divider" />

      <a
        v-for="item in sidebarItems"
        :key="item.href"
        :href="item.href"
        class="admin-dashboard-sidebar__item"
        :title="item.label"
      >
        <span class="admin-dashboard-sidebar__icon">
          <component :is="item.icon" scale="1.3" />
        </span>
        <span class="admin-dashboard-sidebar__label">{{ item.label }}</span>
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

  created(): void {
    this.expanded = window.matchMedia('(min-width: 992px)').matches;
  }

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
        label: T.omegaupTitleAdminGlobalSettings,
        icon: 'BIconGearFill',
      },
    ];
  }
}
</script>

<style lang="scss" scoped>
.admin-dashboard-layout {
  min-height: calc(100vh - 8rem);
  margin-bottom: -3rem;
}

.admin-dashboard-sidebar {
  display: flex;
  flex-direction: column;
  align-items: stretch;
  width: 4.5rem;
  flex-shrink: 0;
  padding: 1.25rem 0;
  background-color: var(--admin-dashboard-sidebar-background-color);
  border-right: 1px solid var(--admin-dashboard-sidebar-border-color);
  transition: width 0.2s ease-in-out;
  overflow: hidden;

  &--expanded {
    width: 19rem;
  }
}

.admin-dashboard-sidebar__toggle {
  display: flex;
  align-items: center;
  width: 100%;
  padding: 0.85rem 1.35rem;
  border-radius: 0;
  color: var(--admin-dashboard-sidebar-item-color);

  &:hover {
    color: var(--admin-dashboard-sidebar-item-hover-color);
    text-decoration: none;
  }

  // Sin recuadro al hacer clic con el mouse...
  &:focus {
    box-shadow: none;
    outline: none;
  }

  // ...pero visible al navegar con teclado.
  &:focus-visible {
    outline: 2px solid var(--admin-dashboard-sidebar-item-hover-color);
    outline-offset: -2px;
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
  color: var(--admin-dashboard-sidebar-item-color);
  text-decoration: none;

  &:hover {
    background-color: var(
      --admin-dashboard-sidebar-item-hover-background-color
    );
    color: var(--admin-dashboard-sidebar-item-hover-color);
    text-decoration: none;
  }

  &:focus-visible {
    outline: 2px solid var(--admin-dashboard-sidebar-item-hover-color);
    outline-offset: -2px;
  }
}

.admin-dashboard-sidebar__icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  width: 1.75rem;
}

.admin-dashboard-sidebar__label {
  min-width: 0;
  margin-left: 1rem;
  font-size: 0.95rem;
  line-height: 1.2;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  opacity: 0;
  transition: opacity 0.15s ease-in-out;
}

.admin-dashboard-sidebar--expanded .admin-dashboard-sidebar__label {
  opacity: 1;
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
