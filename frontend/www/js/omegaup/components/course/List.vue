<template>
  <div class="panel panel-primary">
    <div class="panel-heading">
      <h3 class="panel-title">{{ T.courseList }}</h3>
    </div>

    <template v-for="(typeCourses, key) in courses">
      <div class="page-header">
        <div class="pull-right" v-if="key === 0">
          <a class="btn btn-primary" href="/course/new/">{{ T.courseNew }}</a>
        </div>
        <h1>
          <span>{{ typeCourses.name }}</span>
        </h1>
      </div>

      <omegaup-course-filtered-list
        v-bind:name="key"
        v-bind:courses="typeCourses"
        v-bind:showTabStudent="showTabStudent"
        v-bind:showTabAdmin="showTabAdmin"
      ></omegaup-course-filtered-list>
    </template>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import omegaup from '../../api.js';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';
import course_FilteredList from './FilteredList.vue';

@Component({
  components: {
    'omegaup-course-filtered-list': course_FilteredList,
  },
})
export default class CourseList extends Vue {
  @Prop() courses!: omegaup.CourseType[];
  @Prop() initialActiveTabStudent!: string;
  @Prop() initialActiveTabAdmin!: string;

  T = T;
  UI = UI;
  showTabStudent = '';
  showTabAdmin = '';
}
</script>
