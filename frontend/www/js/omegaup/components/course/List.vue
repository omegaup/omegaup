<template>
  <div>
    <template v-for="(typeCourses, accessMode) in courses">
      <div
        v-if="typeCourses.activeTab !== ''"
        :key="accessMode"
        class="card mb-4"
        :class="accessMode"
      >
        <div class="card-header">
          <h3 class="card-title">{{ getDescription(accessMode) }}</h3>
        </div>

        <omegaup-course-filtered-list
          :courses="typeCourses"
          :activeTab="typeCourses.activeTab"
        ></omegaup-course-filtered-list>
      </div>
    </template>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import course_FilteredList from './FilteredList.vue';

@Component({
  components: {
    'omegaup-course-filtered-list': course_FilteredList,
  },
})
export default class CourseList extends Vue {
  @Prop() courses!: types.StudentCourses;

  T = T;

  getDescription(admissionMode: string): string {
    if (admissionMode === 'public') return T.courseListPublicCourses;
    if (admissionMode === 'student') return T.courseListIStudy;
    return '';
  }
}
</script>
