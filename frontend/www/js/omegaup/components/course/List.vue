<template>
  <div class="panel panel-primary">
    <div class="panel-heading">
      <h3 class="panel-title">{{ T.courseList }}</h3>
    </div>

    <div class="page-header" v-if="isMainUserIdentity">
      <div class="pull-right">
        <a class="btn btn-primary" href="/course/new/">{{ T.courseNew }}</a>
      </div>
      <h1>&nbsp;</h1>
    </div>
    <template
      v-for="typeCourses in courses"
      v-if="typeCourses.activeTab !== ''"
    >
      <div class="page-header">
        <h1>
          <span>{{ typeCourses.description }}</span>
        </h1>
      </div>

      <omegaup-course-filtered-list
        v-bind:courses="typeCourses"
        v-bind:activeTab="typeCourses.activeTab"
      ></omegaup-course-filtered-list>
    </template>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup, T } from '../../omegaup';
import UI from '../../ui.js';
import course_FilteredList from './FilteredList.vue';

@Component({
  components: {
    'omegaup-course-filtered-list': course_FilteredList,
  },
})
export default class CourseList extends Vue {
  @Prop() courses!: omegaup.Course[];
  @Prop() isMainUserIdentity!: boolean;

  T = T;
  UI = UI;
}
</script>
