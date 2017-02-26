<template>
  <div class="omegaup-course-details panel">
    <div v-if="!update" class="panel-heading">
      <h3 class="panel-title">{{ T.courseNew }}</h3>
    </div>
    <div class="panel-body">
      <form class="form" v-on:submit.prevent="onSubmit">
        <div class="row">
          <div class="form-group col-md-8">
            <label>
              {{ T.wordsTitle }}
              <input v-model="name" type="text" class="form-control" />
            </label>
          </div>

          <div class="form-group col-md-4">
            <label>
              {{ T.courseNewFormShortTitle_alias_ }}
              <span data-toggle="tooltip" data-placement="top" v-bind:title="T.courseNewFormShortTitle_alias_Desc" class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
              <input v-model="alias" type="text" class="form-control" v-bind:disabled="update" />
            </label>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-4">
            <label>
              {{ T.courseNewFormStartDate }}
              <span data-toggle="tooltip" data-placement="top" v-bind:title="T.courseNewFormEndDateDesc" class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
              <omegaup-datepicker v-model="startTime"></omegaup-datepicker>
            </label>
          </div>
          <div class="form-group col-md-4">
            <label>
              {{ T.courseNewFormEndDate }}
              <span data-toggle="tooltip" data-placement="top" v-bind:title="T.courseNewFormEndDateDesc" class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
              <omegaup-datepicker v-model="finishTime"></omegaup-datepicker>
            </label>
          </div>
          <div class="form-group col-md-4">
            <label>
              {{ T.courseNewFormShowScoreboard }}
              <span data-toggle="tooltip" data-placement="top" v-bind:title="T.courseNewFormShowScoreboardDesc" class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
              <div class="form-control container">
                <label class="radio-inline"><input type="radio" value="1" v-model="showScoreboard">{{ T.wordsYes }}</label>
                <label class="radio-inline"><input type="radio" value="0" v-model="showScoreboard">{{ T.wordsNo }}</label>
              </div>
            </label>
          </div>
        </div>

        <div class="row">
          <div class="form-group container">
            <label>
              {{ T.courseNewFormDescription }}
              <textarea v-model="description" cols="30" rows="5" class="form-control"></textarea>
            </label>
          </div>
        </div>

        <div class="form-group pull-right">
          <button type="submit" class="btn btn-primary">
            <template v-if="update">{{ T.courseNewFormUpdateCourse }}</template>
            <template v-else>{{ T.courseNewFormScheduleCourse }}</template>
          </button>
          <button v-on:click.prevent="reset" type="reset" class="btn btn-secondary">
            {{ T.wordsCancel }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import DatePicker from '../DatePicker.vue';

export default {
  props: {
    T: Object,
    update: Boolean,
    course: Object,
  },
  data: function() {
    return {
      alias: this.course.alias,
      description: this.course.description,
      finishTime: this.course.finish_time || new Date(),
      showScoreboard: this.course.show_scoreboard || 0,
      startTime: this.course.start_time || new Date(),
      name: this.course.name,
    };
  },
  watch: {
    course: function(val) {
      this.reset();
    },
  },
  methods: {
    reset: function() {
      this.alias = this.course.alias;
      this.description = this.course.description;
      this.finishTime = this.course.finish_time || new Date();
      this.showScoreboard = this.course.show_scoreboard || 0;
      this.startTime = this.course.start_time || new Date();
      this.name = this.course.name;
    },
    onSubmit: function() {
      this.$emit('submit', this);
    },
  },
  components: {
    'omegaup-datepicker': DatePicker,
  },
};
</script>

<style>
.omegaup-course-details .form-group>label {
  width: 100%;
}
</style>
