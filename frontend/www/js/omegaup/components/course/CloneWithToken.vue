<template>
  <div class="omegaup-course-clone card">
    <h2 class="card-header">{{ T.wordsCloneCourse }}</h2>
    <div class="card-body">
      <div class="mb-4">
        <p>
          <span class="font-weight-bold">{{ T.wordsName }}: </span>
          {{ course.name }}
        </p>
        <p>
          <span class="font-weight-bold">{{ T.courseCloneCreatedBy }}: </span>
          <omegaup-username
            :classname="classname"
            :username="username"
            :linkify="true"
          ></omegaup-username>
        </p>
        <div class="d-flex align-items-baseline">
          <span class="font-weight-bold mr-2 text-nowrap">
            {{ T.wordsDescription }}:
          </span>
          <omegaup-markdown
            :full-width="true"
            :markdown="course.description"
            class="flex-grow-1 description-content"
          ></omegaup-markdown>
        </div>
        <li
          v-for="assignment of course.assignments"
          :key="assignment.problemset_id"
        >
          {{ assignment.name }}
        </li>
      </div>
      <omegaup-course-clone
        :initial-alias="aliasWithUsername"
        :initial-name="course.name"
        @clone="
          (alias, name, startTime) =>
            $emit('clone', alias, name, token, startTime)
        "
      ></omegaup-course-clone>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';
import course_Clone from './Clone.vue';
import DatePicker from '../DatePicker.vue';
import omegaup_Username from '../user/Username.vue';
import omegaup_Markdown from '../Markdown.vue';

import {
  FontAwesomeIcon,
  FontAwesomeLayers,
  FontAwesomeLayersText,
} from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
library.add(fas);

@Component({
  components: {
    'omegaup-course-clone': course_Clone,
    'omegaup-datepicker': DatePicker,
    'omegaup-markdown': omegaup_Markdown,
    'omegaup-username': omegaup_Username,
    'font-awesome-icon': FontAwesomeIcon,
    'font-awesome-layers': FontAwesomeLayers,
    'font-awesome-layers-text': FontAwesomeLayersText,
  },
})
export default class CourseCloneWithToken extends Vue {
  @Prop() course!: types.CourseDetails;
  @Prop() username!: string;
  @Prop() classname!: string;
  @Prop() token!: string;
  @Prop() currentUsername!: string;

  T = T;

  get aliasWithUsername(): string {
    return `${this.course.alias}_${this.currentUsername}`;
  }
}
</script>

<style lang="scss" scoped>
/* Allow flex item to shrink below content size for proper text wrapping */
.description-content {
  min-width: 0;
}

/* stylelint-disable-next-line selector-pseudo-element-no-unknown */
::v-deep [data-markdown-statement] {
  word-break: break-word;
  overflow-wrap: break-word;
}

/* stylelint-disable-next-line selector-pseudo-element-no-unknown */
::v-deep [data-markdown-statement] p {
  margin-bottom: 0;
  margin-top: 0;
}
</style>
