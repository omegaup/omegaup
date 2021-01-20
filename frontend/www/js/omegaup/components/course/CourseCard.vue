<template>
  <div class="card ml-3 mr-3 mb-3">
    <div class="m-3 d-flex justify-content-between">
      <h5 class="font-weight-bold m-0">{{ courseName }}</h5>
      <div>{{ dueDate }}</div>
    </div>
    <div class="mx-3 d-flex justify-content-between align-items-center">
      <div>
        <omegaup-markdown :markdown="impartedBy"></omegaup-markdown>
      </div>
      <a :href="`/course/${courseAlias}/`" class="btn btn-primary d-inline-block">{{
        buttonTitle
      }}</a>
    </div>
    <div class="dropdown-divider"></div>
    <div class="mx-3 mt-2 mb-3 d-flex justify-content-between">
      <div v-if="showTopics">
        <details>
          <summary>{{ T.courseCardShowTopics }}</summary>
          <ul>
            <li v-for="assignment in content" :key="assignment.alias">
              {{ assignment.name }}
            </li>
          </ul>
        </details>
      </div>
      <div class="d-flex align-items-center" v-if="true || progress > 0">
        <div class="pr-1 pb-1">{{ T.wordsProgress }}:</div>
        <progress
          :title="`${progress}%`"
          :value="87"
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
import omegaup_Markdown from '../Markdown.vue';

@Component({
  components: {
    'omegaup-markdown': omegaup_Markdown,
  },
})
export default class CourseCard extends Vue {
  @Prop() courseName!: string;
  @Prop() courseAlias!: string;
  @Prop() schoolName!: string;
  @Prop({ default: null }) finishTime!: Date;
  @Prop() progress!: number;
  @Prop() content!: types.CourseAssignment[];
  @Prop() isOpen!: boolean;
  @Prop() loggedIn!: boolean;
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
}
</script>
