<template>
  <div class="col-md-10">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h2 class="panel-title">{{ T.userEmailEditEmail }}</h2>
      </div>
      <div class="panel-body">
        <form class="form-horizontal"
              >
          <div class="form-group">
            <label class="col-md-3 control-label"
                 for="email">{{ T.userEmailEditEmail }}</label>
            <div class="col-md-7">
              <input class="form-control"
                   name='email'
                   size='30'
                   type='text'
                   id="email"
                   v-model="changeemail"
                   >
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-offset-3 col-md-7">
              <button type="button" class="btn btn-primary" v-on:click="change"
                   >{{ T.wordsSaveChanges }}</button>
            </div>
          </div>
        </form>
        <div style="display: none;"
             v-bind:id="wait">{{ T.userEmailEditSaving }}<img src="/media/wait.gif"></div>

      </div>
    </div>
  </div>
</template>

<script>
import {T} from '../../omegaup.js';

export default {
  props: {
    currentemail: String,
  },

  data: function() {
    return {
      T: T, wait: "wait",changeemail:this.currentemail,
    }
  },
  methods: {
    change: function() { this.$emit('submit',this.changeemail);}

  }
}

</script>
