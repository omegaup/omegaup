<template>
  <div></div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import * as ui from '../../ui';
import T from '../../lang';

// Import Bootstrap an BootstrapVue CSS files (order is important)
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';

// Import Only Required Plugins
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
import {
  TabsPlugin,
  CardPlugin,
  DropdownPlugin,
  LayoutPlugin,
  PaginationNavPlugin,
} from 'bootstrap-vue';
import ContestCard from './ContestCard.vue';
Vue.use(TabsPlugin);
Vue.use(CardPlugin);
Vue.use(DropdownPlugin);
Vue.use(LayoutPlugin);
Vue.use(PaginationNavPlugin);
library.add(fas);

export enum ContestTab {
  Current = 'current',
  Future = 'future',
  Past = 'past',
}

export enum ContestOrder {
  None = 'none',
  Title = 'title',
  Ends = 'ends',
  Duration = 'duration',
  Organizer = 'organizer',
  Contestants = 'contestants',
  SignedUp = 'signedup',
}

export enum ContestFilter {
  SignedUp = 'signedup',
  OnlyRecommended = 'recommended',
  All = 'all',
}

@Component({
  components: {
    'omegaup-contest-card': ContestCard,
    FontAwesomeIcon,
  },
})
export default class ArenaContestList extends Vue {
  @Prop({ default: null }) countContests!: number | null;
  @Prop() contests!: types.ContestListItem[];
  @Prop() query!: string;
  @Prop() tab!: ContestTab;
  @Prop() sortOrder!: ContestOrder;
  @Prop({ default: ContestFilter.All }) filter!: ContestFilter;
  @Prop() page!: number;
  T = T;
  ui = ui;
  ContestTab = ContestTab;
  ContestOrder = ContestOrder;
  ContestFilter = ContestFilter;
  currentTab: ContestTab = this.tab;
  currentQuery: string = this.query;
  currentOrder: ContestOrder = this.sortOrder;
  currentFilter: ContestFilter = this.filter;
  currentPage: number = this.page;
  refreshing: boolean = false;

  titleLinkClass(tab: ContestTab) {
    if (this.currentTab === tab) {
      return ['text-center', 'active-title-link'];
    }
    return ['text-center', 'title-link'];
  }

  get numberOfPages(): number {
    if (!this.countContests) {
      // Default value when there are no contests in the list
      return 1;
    }
    return Math.ceil(this.countContests / 10);
  }

  linkGen(pageNum: number) {
    return {
      path: `/arena/`,
      query: {
        page: pageNum,
        tab_name: this.currentTab,
        query: this.query,
        sort_order: this.currentOrder,
        filter: this.filter,
      },
    };
  }

  goToPage(tab: ContestTab) {
    this.refreshing = true;
    this.$emit('go-to-page', {
      path: this.hrefGen({ tab }),
    });
  }

  hrefGen({
    filter,
    sortOrder,
    tab,
    query,
  }: {
    filter?: ContestFilter;
    sortOrder?: ContestOrder;
    tab?: ContestTab;
    query?: string;
  }) {
    if (filter === undefined) {
      filter = this.currentFilter;
    }
    if (sortOrder === undefined) {
      sortOrder = this.currentOrder;
    }
    if (tab === undefined) {
      tab = this.currentTab;
    }

    const queryString = new URLSearchParams({
      page: '1', // Reset page to 1 when changing filters
      tab_name: tab,
      sort_order: sortOrder,
      filter,
    });

    if (!query) {
      return `/arena/?${queryString.toString()}`;
    }

    queryString.set('query', query);
    return `/arena/?${queryString.toString()}`;
  }

  finishContestDate(contest: types.ContestListItem): string {
    return contest.finish_time.toLocaleDateString();
  }

  startContestDate(contest: types.ContestListItem): string {
    return contest.start_time.toLocaleDateString();
  }

  getTimeLink(time: Date): string {
    return `http://timeanddate.com/worldclock/fixedtime.html?iso=${time.toISOString()}`;
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.btn {
  padding: 0.5rem 1rem !important;
}

.card-group-menu {
  position: sticky;
  top: 62px;
  z-index: 10;
  border: none;
  border-bottom: 1px solid var(--arena-scoreboard-hover-color);
  border-radius: 0.25rem 0.25rem 0 0;
}

.title {
  text-align: center;
  font-size: 2rem;
  margin-bottom: 1.8rem;
}

.btn-style {
  border-color: var(--arena-button-border-color);
}

.form-control {
  height: auto;
}

.reset-btn {
  line-height: 1.5;
  color: var(--arena-reset-text-color);
  background-color: var(--arena-runs-table-status-je-ve-font-color);
  background-clip: padding-box;
  border: none;
  border-top: 1px solid var(--arena-reset-border-color);
  border-bottom: 1px solid var(--arena-reset-border-color);
  border-radius: unset;
}

.btn-primary {
  background-color: var(--arena-button-border-color) !important;
}

.sidebar {
  >>> .contest-list-nav {
    background-color: var(
      --arena-contest-list-sidebar-tab-list-background-color
    );

    .active-title-link {
      background-color: var(
        --arena-contest-list-sidebar-tab-list-link-background-color--active
      ) !important;
    }

    .title-link {
      color: var(
        --arena-contest-list-sidebar-tab-list-link-font-color
      ) !important;
    }
  }
}

.empty-category {
  text-align: center;
  font-size: 200%;
  margin: 1em;
  color: var(--arena-contest-list-empty-category-font-color);
}

.btns-group {
  justify-content: flex-end;

  .dropdown {
    margin-right: 1rem;
  }
}

.line {
  height: 49px;
  background: var(
    --arena-submissions-list-skeletonloader-initial-background-color
  );
  border-radius: 8px;
  animation: loading 1.5s infinite;
}

@keyframes loading {
  0% {
    background: var(
      --arena-submissions-list-skeletonloader-initial-background-color
    );
  }
  50% {
    background: var(
      --arena-submissions-list-skeletonloader-final-background-color
    );
  }
  100% {
    background: var(
      --arena-submissions-list-skeletonloader-initial-background-color
    );
  }
}

@media screen and (max-width: 768px) {
  .title {
    font-size: 1.5rem;
    text-align: center;
  }

  .tabs {
    flex-direction: column;
  }

  .btns-group {
    justify-content: flex-start;

    .dropdown {
      flex: 1;
      gap: 1rem;
      margin-right: 0.8rem;
    }
  }
}
</style>
