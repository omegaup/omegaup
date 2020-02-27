<template>
  <div class="panel panel-default">
    <div class="jumbotron no-bottom-margin">
      <h1 class="text-center">{{ T.frontPageWelcome }}</h1>
      <p class="top-margin">{{ T.frontPageDescription }}</p>
      <div class="text-center">
        <a href="/contest/new/" class="btn btn-primary btn-lg">{{
          T.frontPageCreateContestButton
        }}</a>
      </div>
      <p class="text-center top-margin">{{ T.frontPageIntroduction }}</p>
      <div class="text-center">
        <a
          href="http://blog.omegaup.com/category/omegaup/omegaup-101/"
          class="btn btn-primary btn-lg"
          >{{ T.frontPageIntroductionButton }}</a
        >
      </div>
      <p class="text-center top-margin">{{ T.frontPageJoinCourse }}</p>
      <div class="text-center">
        <a href="/course/introduccion_a_cpp" class="btn btn-primary btn-lg">{{
          T.frontPageJoinCourseButton
        }}</a>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { T } from '../../omegaup.js';

@Component
export default class Welcome extends Vue {
  T = T;
}
</script>
