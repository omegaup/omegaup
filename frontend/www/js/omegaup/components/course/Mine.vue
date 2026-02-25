<template>
  <div class="card">

    <div class="card-header d-flex justify-content-between align-items-center">
      <h3 class="mb-0">{{ T.courseListAdminCourses }}</h3>

      <a
        v-if="isMainUserIdentity"
        class="btn btn-primary"
        href="/course/new/"
      >
        {{ T.courseNew }}
      </a>
    </div>

    <div class="card-body pb-0"></div>

    <template v-if="courses.admin.activeTab !== ''">
      <omegaup-course-filtered-list
        :courses="courses.admin"
        :active-tab="courses.admin.activeTab"
        :show-percentage="false"
      ></omegaup-course-filtered-list>
    </template>

  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';
import * as ui from '../../ui';
import course_FilteredList from './FilteredList.vue';

@Component({
  components: {
    'omegaup-course-filtered-list': course_FilteredList,
  },
})
export default class Mine extends Vue {
  @Prop() courses!: types.AdminCourses;
  @Prop() isMainUserIdentity!: boolean;

  T = T;
  ui = ui;
}
</script>
