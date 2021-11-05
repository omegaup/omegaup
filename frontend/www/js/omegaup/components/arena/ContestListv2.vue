<template>
  <div>
    <div class="col-sm-12">
      <h1 class="title">{{ T.wordsContests }}</h1>
    </div>
    <b-card no-body>
      <b-tabs
        v-model="activeTab"
        class="sidebar"
        pills
        card
        vertical
        nav-wrapper-class="contest-list-nav col-sm-4 col-md-2"
      >
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <form :action="queryUrl" method="GET">
                <div class="input-group">
                  <input
                    v-model="query"
                    class="form-control"
                    type="text"
                    name="query"
                    autocomplete="off"
                    :placeholder="T.wordsKeyword"
                  />
                  <div class="input-group-append">
                    <input
                      class="btn btn-primary btn-md active"
                      type="submit"
                      :value="T.wordsSearch"
                    />
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
        <b-tab
          ref="currentContestTab"
          :title="T.contestListCurrent"
          :title-link-class="titleLinkClass(ContestTab.Current)"
        >
          {{ contests.current }}
        </b-tab>
        <b-tab
          ref="futureContestTab"
          :title="T.contestListFuture"
          :title-link-class="titleLinkClass(ContestTab.Future)"
        >
          {{ contests.future }}
        </b-tab>
        <b-tab
          ref="pastContestTab"
          :title="T.contestListPast"
          :title-link-class="titleLinkClass(ContestTab.Past)"
        >
          {{ contests.past }}
        </b-tab>
      </b-tabs>
    </b-card>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';

// Import Bootstrap an BootstrapVue CSS files (order is important)
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';

// Import Only Required Plugins
import { TabsPlugin, CardPlugin } from 'bootstrap-vue';
Vue.use(TabsPlugin);
Vue.use(CardPlugin);

export enum ContestTab {
  Current = 0,
  Future = 1,
  Past = 2,
}

@Component({
  components: {},
})
export default class ArenaContestList extends Vue {
  @Prop() contests!: types.ContestList;
  @Prop() initialQuery!: string;
  T = T;
  ContestTab = ContestTab;
  activeTab: ContestTab = window.location.hash
    ? parseInt(window.location.hash.substr(1))
    : ContestTab.Current;
  query = this.initialQuery;

  titleLinkClass(tab: ContestTab) {
    if (this.activeTab === tab) {
      return ['text-center', 'active-title-link'];
    } else {
      return ['text-center', 'title-link'];
    }
  }

  get queryUrl(): string {
    return `/arenav2/#${this.activeTab}`;
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.sidebar {
  /deep/ .contest-list-nav {
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
</style>
