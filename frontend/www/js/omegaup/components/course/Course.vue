<template>
  <div class="card ml-3 mr-3 mb-3">
    <div class="m-3 d-flex justify-content-between">
      <h5 class="font-weight-bold m-0">
        <a :href="`/course/${courseAlias}/`">{{ courseName }}</a>
      </h5>
      <div class="font-weight-bold" :class="isPublic ? 'public' : 'student'">
        {{ dueDate }}
      </div>
    </div>
    <div class="mx-3 d-flex justify-content-between align-items-center">
      <div>
        <omegaup-markdown :markdown="impartedBy"></omegaup-markdown>
      </div>
      <a
        :href="`/course/${courseAlias}/`"
        class="btn btn-primary d-inline-block text-white"
        >{{ buttonTitle }}</a
      >
    </div>
    <div class="dropdown-divider"></div>
    <div
      class="mx-3 mt-2 mb-3 d-flex justify-content-between align-items-start"
    >
      <div v-if="showTopics" class="w-100">
        <details>
          <summary>{{ T.courseCardShowTopics }}</summary>
          <ul>
            <li v-for="assignment in content" :key="assignment.alias">
              {{ assignment.name }}
            </li>
          </ul>
        </details>
        <details v-if="isPublic">
          <summary>{{ T.wordsCloneThisCourse }}</summary>
          <omegaup-course-clone
            :initial-alias="aliasWithUsername"
            :initial-name="courseName"
            @clone="
              (alias, name, startTime) => $emit('clone', alias, name, startTime)
            "
          ></omegaup-course-clone>
        </details>
      </div>
      <div v-if="progress > 0" class="d-flex align-items-center">
        <div class="pr-1 pb-1">{{ T.wordsProgress }}:</div>
        <progress
          :title="`${progress}%`"
          :value="progress"
          max="100"
        ></progress>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import * as time from '../../time';
import * as ui from '../../ui';
import T from '../../lang';
import course_Clone from './Clone.vue';
import omegaup_Markdown from '../Markdown.vue';

@Component({
  components: {
    'omegaup-course-clone': course_Clone,
    'omegaup-markdown': omegaup_Markdown,
  },
})
export default class Course extends Vue {
  @Prop() currentUsername!: string;
  @Prop() courseName!: string;
  @Prop() courseAlias!: string;
  @Prop() schoolName!: string;
  @Prop({ default: null }) finishTime!: Date;
  @Prop() progress!: number;
  @Prop() content!: types.CourseAssignment[];
  @Prop() isOpen!: boolean;
  @Prop() loggedIn!: boolean;
  @Prop({ default: false }) isPublic!: boolean;
  @Prop({ default: false }) showTopics!: boolean;

  T = T;

  get buttonTitle(): string {
    if (!this.loggedIn) {
      return T.courseCardSeeContent;
    }
    if (this.isOpen) {
      return T.courseCardCourseResume;
    }
    return T.startCourse;
  }

  get dueDate(): string {
    if (!this.finishTime) return T.wordsUnlimitedDuration;
    return ui.formatString(T.courseCardDueDate, {
      due_date: time.formatFutureDateRelative(this.finishTime),
    });
  }

  get impartedBy(): string {
    return ui.formatString(T.courseCardImpartedBy, {
      school_name: ui.escape(this.schoolName),
    });
  }

  get aliasWithUsername(): string {
    return `${this.courseAlias}_${this.currentUsername}`;
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.public {
  color: $omegaup-pink;
}

.student {
  color: $omegaup-blue;
}
</style>
