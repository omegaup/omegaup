<template>
  <div class="col mb-3">
    <div class="card">
      <div class="row no-gutters">
        <div class="col-sm-2 public-course-card"></div>
        <div class="col-sm-10">
          <div
            class="card-body d-flex flex-column h-100 justify-content-between"
          >
            <div>
              <h5 class="card-title mb-0">
                <a v-if="loggedIn" :href="`/course/${course.alias}/`">{{
                  course.name
                }}</a>
                <template v-else>{{ course.name }}</template>
              </h5>
              <p class="card-text">
                <small>{{ course.school_name }}</small>
              </p>
            </div>
            <div class="card-text course-data">
              <p class="mb-2">
                {{
                  ui.formatString(T.publicCourseCardMetrics, {
                    lessonCount: course.lessonCount,
                    studentCount:
                      course.studentCount >= 1000
                        ? `${(course.studentCount / 1000).toFixed(1)}k`
                        : course.studentCount,
                  })
                }}
              </p>
              <p class="mb-2">{{ courseLevelText(course.level) }}</p>
              <div v-if="loggedIn" class="text-center mt-1">
                <a
                  class="btn btn-primary text-white"
                  role="button"
                  :href="`/course/${course.alias}/`"
                >
                  {{
                    course.alreadyStarted
                      ? T.courseCardCourseResume
                      : T.wordsStart
                  }}
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';

import omegaup_Markdown from '../Markdown.vue';

@Component({
  components: {
    'omegaup-markdown': omegaup_Markdown,
  },
})
export default class CourseCardPublic extends Vue {
  @Prop() course!: types.CourseCardPublic;
  @Prop({ default: false }) loggedIn!: boolean;

  T = T;
  ui = ui;

  courseLevelText(level: null | string): string {
    switch (level) {
      case 'introductory':
        return T.courseCardPublicLevelIntroductory;
      case 'intermediate':
        return T.courseCardPublicLevelIntermediate;
      case 'advanced':
        return T.courseCardPublicLevelAdvanced;
      default:
        return '';
    }
  }
}
</script>
