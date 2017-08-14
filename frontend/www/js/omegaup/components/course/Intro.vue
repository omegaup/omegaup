<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h2 class="panel-title">{{ T.courseDetails }}</h2>
    </div>
    <div class="panel-body">
      <h2 id="name">{{ name }}</h2>
      <p id="description">{{ description }}</p>
      <div class="text-center">
        <form id="start-course-form"
              name="start-course-form"
              v-on:submit.prevent="">
          <button class="btn btn-primary btn-lg"
                id="start-course-submit"
                type="button"
                v-on:click="onSubmit">{{ T.startCourse }}</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import {T} from '../../omegaup.js';

export default {
  props: {name: String, description: String},
  data: function() {
    return { T: T, }
  },
  methods: {onSubmit() { this.$emit('submit', this);}}
}
</script>
