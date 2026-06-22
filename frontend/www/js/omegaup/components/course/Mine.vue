<template>
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h3 class="mb-0">{{ T.courseListAdminCourses }}</h3>
      <a
        v-if="isMainUserIdentity && hasCourses"
        class="btn btn-primary"
        href="/course/new/"
      >
        {{ T.courseNew }}
      </a>
    </div>
    <div class="card-body pb-0"></div>
    <template v-if="hasCourses">
      <omegaup-course-filtered-list
        :courses="courses.admin"
        :active-tab="courses.admin.activeTab"
        :show-percentage="false"
      />
    </template>
    <omegaup-common-empty-state
      v-else
      icon="graduation-cap"
      :title="T.courseListEmptyTitle"
      :description="T.courseListEmptyDescription"
      :button-text="isMainUserIdentity ? T.courseNew : ''"
      button-link="/course/new/"
    />
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';
import * as ui from '../../ui';
import course_FilteredList from './FilteredList.vue';
import common_EmptyState from '../common/EmptyState.vue';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';

library.add(fas);

@Component({
  components: {
    'omegaup-course-filtered-list': course_FilteredList,
    'font-awesome-icon': FontAwesomeIcon,
    'omegaup-common-empty-state': common_EmptyState,
  },
})
export default class Mine extends Vue {
  @Prop() courses!: types.AdminCourses;
  @Prop() isMainUserIdentity!: boolean;

  T = T;
  ui = ui;

  get hasCourses(): boolean {
    const filtered = this.courses.admin.filteredCourses;

    if (!filtered) return false;

    return Object.values(filtered).some(
      (group: types.CoursesByTimeType) => group.courses?.length > 0,
    );
  }
}
</script>
