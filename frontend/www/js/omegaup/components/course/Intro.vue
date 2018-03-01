<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h2 class="panel-title">{{ T.courseDetails }}</h2>
    </div>
    <div class="panel-body">
      <h2 id="name">{{ name }}</h2>
      <p id="description">{{ description }}</p>
      <p v-html="T.courseBasicInformationNeeded"
         v-if="needsBasicInformation"></p>
      <template v-if="requestsUserInformation == 'optional'">
        <p v-html="T.courseUserInformationOptional"></p>
      </template>
      <template v-if="requestsUserInformation == 'required'">
        <p v-html="T.courseUserInformationRequired"></p>
      </template><label><input type="radio"
             v-bind:value="1"
             v-model="shareUserInformation"> {{ T.wordsYes }}</label> <label><input type="radio"
             v-bind:value="0"
             v-model="shareUserInformation"> {{ T.wordsNo }}</label>
      <div class="text-center">
        <form id="start-course-form"
              name="start-course-form"
              v-on:submit.prevent="">
          <button class="btn btn-primary btn-lg"
                id="start-course-submit"
                type="button"
                v-bind:disabled=
                "needsBasicInformation || (requestsUserInformation == 'optional' &amp;&amp; shareUserInformation == undefined) || (requestsUserInformation == 'required' &amp;&amp; shareUserInformation != 1)"
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
    requestsUserInformation: Boolean
  },
  data: function() {
    return { T: T, shareUserInformation: undefined }
  },
  methods: {onSubmit() { this.$emit('submit', this);}}
}
</script>
