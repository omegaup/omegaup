<template>
  <div class="card ml-3 mr-3 mb-3">
    <div class="m-3">
      <a
        class="font-weight-bold float-left"
        :href="`/course/${courseAlias}/`"
        >{{ courseName }}</a
      >
      <div class="float-right">{{ dueDate }}</div>
    </div>
    <div class="m-3">
      <div class="float-left align-middle">
        <omegaup-markdown :markdown="impartedBy"></omegaup-markdown>
      </div>
      <div class="float-right">
        <a :href="`/course/${courseAlias}/`" class="btn btn-primary">{{
          buttonTitle
        }}</a>
      </div>
    </div>
    <hr class="ml-3 mr-3" />
    <div class="m-3">
      <div v-if="progress > 0" class="float-right">
        {{ T.wordsProgress }}:
        <progress
          :title="`${progress}%`"
          :value="progress"
          max="100"
        ></progress>
      </div>
      <div v-if="showTopics" class="float-left align-middle">
        <details>
          <summary>{{ T.courseCardShowTopics }}</summary>
          <ul>
            <li v-for="assignment in content" :key="assignment.alias">
              {{ assignment.name }}
            </li>
          </ul>
        </details>
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
