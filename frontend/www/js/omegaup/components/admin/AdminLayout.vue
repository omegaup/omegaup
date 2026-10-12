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
        :class="{ 'admin-dashboard-sidebar__item--active': isActive(item) }"
        :title="item.label"
        :aria-current="isActive(item) ? 'page' : null"
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
  BIconHouseDoorFill,
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
  adminOnly?: boolean;
}

@Component({
  components: {
    BIconList,
    BIconChevronDoubleLeft,
    BIconHouseDoorFill,
    BIconPersonLinesFill,
    BIconClockHistory,
    BIconHeadset,
    BIconGearFill,
  },
})
export default class AdminLayout extends Vue {
  @Prop({ default: '' }) currentModule!: string;
  @Prop({ default: false }) isAdmin!: boolean;
  @Prop({ default: 'home' }) selectedModule!: string;

  T = T;

  expanded = false;
  isLargeScreen = false;

  created(): void {
    this.isLargeScreen = window.matchMedia('(min-width: 992px)').matches;
    this.expanded = this.isLargeScreen;
  }

  mounted(): void {
    window.addEventListener('resize', this.onResize);
  }

  beforeDestroy(): void {
    window.removeEventListener('resize', this.onResize);
  }

  onResize(): void {
    const isLargeScreen = window.matchMedia('(min-width: 992px)').matches;
    if (isLargeScreen !== this.isLargeScreen) {
      this.isLargeScreen = isLargeScreen;
      this.expanded = isLargeScreen;
    }
  }

  // Los href con # cambian de sección sin recargar, los demás aún son páginas aparte.
  get sidebarItems(): SidebarItem[] {
    const items: SidebarItem[] = [
      {
        href: '#home',
        label: T.omegaupTitleAdminOperations,
        icon: 'BIconHouseDoorFill',
      },
      {
        href: '#settings',
        label: T.omegaupTitleAdminGlobalSettings,
        icon: 'BIconGearFill',
        adminOnly: true,
      },
      {
        href: '/admin/user/',
        label: T.omegaupTitleAdminUsers,
        icon: 'BIconPersonLinesFill',
      },
      {
        href: '/admin/crons/',
        label: T.omegaupTitleAdminCrons,
        icon: 'BIconClockHistory',
        adminOnly: true,
      },
      {
        href: '/admin/support/',
        label: T.omegaupTitleSupportDashboard,
        icon: 'BIconHeadset',
      },
    ];
    return items.filter((item) => !item.adminOnly || this.isAdmin);
  }

  // Solo los items con # (ya integrados al dashboard) se resaltan.
  isActive(item: SidebarItem): boolean {
    return item.href === `#${this.selectedModule}`;
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

  &:focus {
    box-shadow: none;
    outline: none;
  }

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

  &--active {
    background-color: var(
      --admin-dashboard-sidebar-item-hover-background-color
    );
    color: var(--admin-dashboard-sidebar-item-hover-color);
    font-weight: 600;
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
