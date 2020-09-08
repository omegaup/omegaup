<template>
  <div class="omegaup-course-clone card">
    <div class="card-header">
      <h2>{{ T.wordsCloneCourse }}</h2>
    </div>
    <div class="card-body">
      <div class="mb-4">
        <p>
          <span class="font-weight-bold">{{ T.wordsName }}: </span>
          {{ course.name }}
        </p>
        <p>
          <span class="font-weight-bold">{{ T.courseCloneCreatedBy }}: </span>
          <omegaup-username
            v-bind:classname="classname"
            v-bind:username="username"
            v-bind:linkify="true"
          ></omegaup-username>
        </p>
        <p>
          <span class="font-weight-bold">{{ T.wordsDescription }}: </span>
          {{ course.description }}
        </p>
        <li
          v-for="assignment of course.assignments"
          v-bind:key="assignment.problemset_id"
        >
          {{ assignment.name }}
        </li>
      </div>
      <omegaup-course-clone
        v-bind:initial-alias="course.alias"
        v-bind:initial-name="course.name"
        v-on:clone="
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

  T = T;
}
</script>
