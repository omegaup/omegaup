<template>
  <div class="panel panel-primary">
    <div class="panel-body">
      <form class="contest-publish-form"
            v-on:submit.prevent="onSubmit">
        <div class="form-group">
          <label>{{T.contestNewFormAdmissionMode}}</label> <select class="form-control"
               name="admission-mode"
               v-model="admissionMode">
            <option value="private">
              {{T.wordsPrivate}}
            </option>
            <option value="registration">
              {{T.wordsRegistration}}
            </option>
            <option value="public">
              {{T.wordsPublic}}
            </option>
          </select>
          <p class="help-block"><span v-html="T.contestNewFormAdmissionModeDescription"></span></p>
        </div><button class="btn btn-primary change-admission-mode"
              type="submit">{{T.wordsSaveChanges}}</button>
      </form>
    </div>
  </div>
</template>

<script>
import {T} from '../../omegaup.js';

export default {
  props: {
    data: Object,
  },
  data: function() {
    return {
      contest: this.data,
      T: T,
      admissionMode: this.data.admission_mode,
    };
  },
  watch: {
    contest: function() { this.admissionMode = this.contest.admission_mode;},
  },
  methods: {
    onSubmit: function() { this.$parent.$emit('update-admission-mode', this);},
  },
};
</script>
