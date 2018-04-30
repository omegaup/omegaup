<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h2 class="panel-title">{{ T.courseDetails }}</h2>
    </div>
    <div class="panel-body">
      <h2 name="name">{{ name }}</h2>
      <p name="description">{{ description }}</p>
      <p v-html="T.courseBasicInformationNeeded"
         v-if="needsBasicInformation"></p>
      <template v-if="requestsUserInformation != 'no'">
        <p v-html="T.courseUserInformationOptional"
           v-if="requestsUserInformation == 'optional'"></p>
        <p v-html="T.courseUserInformationRequired"
           v-if="requestsUserInformation == 'required'"></p><label><input type="radio"
               v-bind:value="1"
               v-model="shareUserInformation"> {{ T.wordsYes }}</label> <label><input type="radio"
               v-bind:value="0"
               v-model="shareUserInformation"> {{ T.wordsNo }}</label>
      </template>
      <template v-if="showAcceptTeacher">
        <p v-html="T.courseUserAcceptTeacher"></p><label><input type="radio"
               v-model="acceptTeacher"
               value="yes"> {{ T.wordsYes }}</label> <label><input type="radio"
               v-model="acceptTeacher"
               value="no"> {{ T.wordsNo }}</label>
      </template>
      <div class="text-center">
        <form v-on:submit.prevent="">
          <button class="btn btn-primary btn-lg"
                name="start-course-submit"
                type="button"
                v-bind:disabled=
                "needsBasicInformation || (requestsUserInformation == 'optional' &amp;&amp; shareUserInformation == undefined) || (requestsUserInformation == 'required' &amp;&amp; shareUserInformation != 1 || acceptTeacher == undefined)"
                v-on:click="onSubmit">{{ T.startCourse }}</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import {T} from '../../omegaup.js';

export default {
  props: {
    name: String,
    description: String,
    needsBasicInformation: Boolean,
    requestsUserInformation: String,
    showAcceptTeacher: Boolean
  },
  data: function() {
    return { T: T, shareUserInformation: undefined, acceptTeacher: undefined }
  },
  methods: {onSubmit() { this.$emit('submit', this);}}
}
</script>
