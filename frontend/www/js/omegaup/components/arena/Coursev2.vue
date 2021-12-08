<template>
  <b-container fluid class="p-5">
    <div>
      <a class="mb-2" :href="`/course/${course.alias}/`">
        <b-icon-chevron-left></b-icon-chevron-left>
        {{ T.arenaCourseAllContent }}
      </a>
      <h2 class="mb-0">{{ course.name }}</h2>
      <h4>{{ assignment.name }}</h4>
    </div>
    <b-row class="px-3 mt-4 align-items-start">
      <b-card no-body class="col-md-3 col-lg-2 p-0 text-center">
        <b-card-header header-tag="nav" class="border-0">
          <b-nav card-header pills justified>
            <b-nav-item
              :href="`#${Tabs.Summary}`"
              :active="currentSelectedTab === Tabs.Summary"
              @click="currentSelectedTab = Tabs.Summary"
              >{{ T.wordsSummary }}</b-nav-item
            >
            <b-nav-item
              :href="`#${Tabs.Ranking}`"
              :active="currentSelectedTab === Tabs.Ranking"
              @click="currentSelectedTab = Tabs.Ranking"
              >{{ T.wordsRanking }}</b-nav-item
            >
          </b-nav>
          <b-nav card-header pills vertical>
            <b-nav-item
              v-for="problem in problems"
              :key="problem.alias"
              :href="`/course/${encodeURIComponent(
                course.alias,
              )}/arena/${encodeURIComponent(
                assignment.alias,
              )}/problem/${encodeURIComponent(problem.alias)}/`"
              :active="
                !currentSelectedTab &&
                currentProblem &&
                currentProblem.alias === problem.alias
              "
              >{{
                ui.formatString(T.arenaCourseProblemTitle, {
                  letter: problem.letter,
                  title: problem.title,
                })
              }}</b-nav-item
            >
          </b-nav>
        </b-card-header>
      </b-card>

      <b-col md="9" lg="10" class="mt-3 mt-md-0">
        <!-- This is just for the case of the summary -->
        <omegaup-markdown
          v-if="currentSelectedTab === Tabs.Summary"
          :markdown="assignment.description"
          :full-width="true"
        ></omegaup-markdown>
        <omegaup-arena-scoreboard
          v-if="currentSelectedTab === Tabs.Ranking"
          :show-invited-users-filter="false"
          :problems="scoreboard.problems"
          :ranking="scoreboard.ranking"
          :last-updated="scoreboard.time"
        >
          <template #scoreboard-header>
            <h3 class="text-center">{{ T.wordsRanking }}</h3>
          </template>
        </omegaup-arena-scoreboard>
      </b-col>
    </b-row>
  </b-container>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';
import omegaup_Markdown from '../Markdown.vue';
import arena_Scoreboard from './Scoreboard.vue';

import { BIconChevronLeft, BootstrapVue } from 'bootstrap-vue';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
Vue.use(BootstrapVue);

export enum Tabs {
  Summary = 'summary',
  Ranking = 'ranking',
}

@Component({
  components: {
    BIconChevronLeft,
    'omegaup-markdown': omegaup_Markdown,
    'omegaup-arena-scoreboard': arena_Scoreboard,
  },
})
export default class ArenaCourse extends Vue {
  @Prop() course!: types.ArenaCourseDetails;
  @Prop() assignment!: types.ArenaCourseAssignment;
  @Prop() problems!: types.ArenaCourseProblem[];
  @Prop() currentProblem!: types.ArenaCourseCurrentProblem;
  @Prop({ default: Tabs.Summary }) selectedTab!: string | null;
  @Prop() scoreboard!: types.Scoreboard;

  T = T;
  ui = ui;
  Tabs = Tabs;
  currentSelectedTab: string | null = this.selectedTab;

  @Watch('selectedTab')
  onSelectedTabChanged(newValue: string | null) {
    this.currentSelectedTab = newValue;
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
</style>
