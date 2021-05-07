<template>
  <div>
    <div class="col-sm-12">
      <h1 class="title">{{ T.wordsContests }}</h1>
    </div>
    <b-card no-body>
      <b-tabs
        v-model="tabIndex"
        pills
        card
        vertical
        nav-wrapper-class="custom-nav col-sm-4 col-md-2"
      >
        <b-tab
          ref="currentContestTab"
          :title="T.wordsCurrent"
          :title-link-class="titleLinkClass(0)"
          active
        >
          {{ contests.current }}
        </b-tab>
        <b-tab
          ref="futureContestTab"
          :title="T.wordsUpcoming"
          :title-link-class="titleLinkClass(1)"
        >
          {{ contests.future }}
        </b-tab>
        <b-tab
          ref="pastContestTab"
          :title="T.wordsPast"
          :title-link-class="titleLinkClass(2)"
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

@Component({
  components: {},
})
export default class ArenaContestList extends Vue {
  @Prop() contests!: types.ContestList;
  T = T;
  tabIndex = 0;

  titleLinkClass(idx: number) {
    if (this.tabIndex === idx) {
      return ['text-center', 'active-title-link'];
    } else {
      return ['text-center', 'title-link'];
    }
  }
}
</script>

<style type="text/css">
.title {
  font-size: 32px;
  margin-bottom: 30px;
}
.custom-nav {
  background-color: #efefef;
}
.custom-nav .active-title-link {
  background-color: #5588dd !important;
}
.custom-nav .title-link {
  color: #5588dd !important;
}
</style>
