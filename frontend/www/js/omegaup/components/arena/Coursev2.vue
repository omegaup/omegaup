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
    <b-row class="px-3 mt-4">
      <b-card no-body class="col-md-3 col-lg-2 p-0 text-center">
        <b-card-header header-tag="nav">
          <b-nav card-header pills justified>
            <b-nav-item
              :href="`/course/${course.alias}/arena/${assignment.alias}/`"
              :active="currentProblem === null"
              >{{ T.wordsSummary }}</b-nav-item
            >
            <b-nav-item>{{ T.wordsRanking }}</b-nav-item>
          </b-nav>
          <b-nav card-header pills vertical>
            <b-nav-item
              v-for="problem in problems"
              :key="problem.alias"
              :href="`/course/${course.alias}/arena/${assignment.alias}/problem/${problem.alias}/`"
              :active="currentProblem && currentProblem.alias === problem.alias"
              >{{ `${problem.letter}. ${problem.title}` }}</b-nav-item
            >
          </b-nav>
        </b-card-header>
      </b-card>

      <b-col md="9" lg="10">
        <!-- This is just for the case of the resume -->
        <omegaup-markdown
          :markdown="assignment.description"
          :full-width="true"
        ></omegaup-markdown>
        <!-- TODO: Ranking, Problem -->
      </b-col>
    </b-row>
  </b-container>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import omegaup_Markdown from '../Markdown.vue';

import { BIconChevronLeft, BootstrapVue } from 'bootstrap-vue';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
Vue.use(BootstrapVue);

@Component({
  components: {
    BIconChevronLeft,
    'omegaup-markdown': omegaup_Markdown,
  },
})
export default class ArenaCourse extends Vue {
  @Prop() course!: types.ArenaCourseDetails;
  @Prop() assignment!: types.ArenaCourseAssignment;
  @Prop() problems!: types.ArenaCourseProblem[];
  @Prop() currentProblem!: types.ArenaCourseCurrentProblem;

  T = T;
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
</style>
