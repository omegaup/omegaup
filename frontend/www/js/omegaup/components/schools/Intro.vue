<template>
  <div class="omegaup-schools-intro">
    <div class="page-header header-bg">
      <h1>{{ T.schoolsLearnProgBySolvProblems }}</h1>
      <p>
        <a class="btn btn-primary" href="/course/" role="button">{{
          T.schoolsStartUsingCourses
        }}</a>
      </p>
    </div>
    <div class="row">
      <div class="col-sm-6 col-md-4">
        <div class="thumbnail">
          <img src="/media/u30.png" alt="Create your course" />
          <div class="caption">
            <h3>{{ T.schoolsCreateYourCourse }}</h3>
            <p>{{ T.schoolsCreateCoursesThatMeetNeeds }}</p>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-md-4">
        <div class="thumbnail">
          <img src="/media/u35.png" alt="Manage student progress" />
          <div class="caption">
            <h3>{{ T.schoolsManageProgressOfStudents }}</h3>
            <p>{{ T.schoolsCreateCoursesThatMeetNeeds }}</p>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-md-4">
        <div class="thumbnail">
          <img src="/media/u30.png" alt="Create your problems" />
          <div class="caption">
            <h3>{{ T.schoolsCreateYourProblems }}</h3>
            <p>{{ T.schoolsCreateCoursesThatMeetNeeds }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';

import T from '../../lang';

@Component
export default class Intro extends Vue {
  T = T;
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
.header-bg {
  background-image: url(/media/teacher3.jpg);
  background-position-x: -200px;
  background-position-y: -270px;
  color: var(--schools-intro-header-text-color);
  height: 250px;
  padding: 10px;
  text-align: center;
}

.header-bg h1 {
  font-size: 40px;
  margin-top: 80px;
  text-shadow: 1px 1px var(--schools-intro-header-title-text-shadow-color);
}

body {
  background: var(--schools-intro-body-background-color);
}
</style>
