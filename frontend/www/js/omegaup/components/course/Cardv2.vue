<template>
  <div class="col mb-3">
    <div class="card">
      <div class="row no-gutters">
        <div class="col-sm-2" :class="`${type}-course-card`"></div>
        <div class="col-sm-10">
          <div
            class="card-body d-flex flex-column h-100"
            :class="{ 'justify-content-between': type === CourseType.Public }"
          >
            <div>
              <h5 class="card-title mb-0">{{ course.name }}</h5>
              <p v-if="type === CourseType.Public" class="card-text">
                <small>{{ course.school_name }}</small>
              </p>
            </div>
            <div
              class="card-text course-data"
              :class="{
                'mt-3 h-100 d-flex flex-column justify-content-around':
                  type === CourseType.Enrolled,
              }"
            >
              <template v-if="type === CourseType.Public">
                <p class="mb-0">
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
                <p class="mb-0">{{ courseLevelText(course.level) }}</p>
              </template>
              <div v-else-if="type === CourseType.Enrolled">
                <omegaup-markdown
                  v-if="course.school_name"
                  :full-width="true"
                  :markdown="
                    ui.formatString(T.courseCardImpartedBy, {
                      school_name: course.school_name,
                    })
                  "
                >
                </omegaup-markdown>
                <div class="d-flex justify-content-between align-items-center">
                  {{ T.courseCardProgress }}
                  <div class="progress w-75">
                    <div
                      class="progress-bar"
                      role="progressbar"
                      :aria-valuenow="course.progress"
                      aria-valuemin="0"
                      aria-valuemax="100"
                      :style="`width: ${course.progress}%`"
                    ></div>
                  </div>
                </div>
              </div>
              <div class="text-center mt-1">
                <a
                  class="btn btn-primary text-white"
                  role="button"
                  :href="`/course/${course.alias}/`"
                  >{{ buttonMessage }}</a
                >
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

export enum CourseType {
  Finished = 'finished',
  Public = 'public',
  Enrolled = 'enrolled',
}

@Component({
  components: {
    'omegaup-markdown': omegaup_Markdown,
  },
})
export default class CourseCard extends Vue {
  @Prop() course!:
    | types.CourseCardEnrolled[]
    | types.CourseCardPublic[]
    | types.CourseCardFinished[];
  @Prop() type!: CourseType;

  T = T;
  ui = ui;
  CourseType = CourseType;

  get buttonMessage(): string {
    switch (this.type) {
      case CourseType.Finished:
        return T.wordsSeeCourse;
      case CourseType.Enrolled:
        return T.courseCardCourseResume;
      default:
        return T.wordsStart;
    }
  }

  courseLevelText(level: null | string): string {
    switch (level) {
      case 'introductory':
        return `★ ${T.courseLevelIntroductory}`;
      case 'intermediate':
        return `★★ ${T.courseLevelIntroductory}`;
      case 'advanced':
        return `★★★ ${T.courseLevelAdvanced}`;
      default:
        return '';
    }
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.card > .row.no-gutters {
  background-color: $omegaup-white;
  height: 12.5rem;
  overflow-y: auto;

  .course-data p {
    font-size: 0.9rem;
  }

  .public-course-card {
    background-color: $omegaup-blue;
  }

  .enrolled-course-card {
    background-color: $omegaup-pink--lighter;
  }

  .finished-course-card {
    background-color: $omegaup-grey--lighter;
  }

  .progress-bar {
    background-color: $omegaup-yellow;
  }
}
</style>
