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
        <p>
          <span class="font-weight-bold">{{ T.wordsDescription }}: </span>
          <span>{{ course.description }}</span>
        </p>
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
