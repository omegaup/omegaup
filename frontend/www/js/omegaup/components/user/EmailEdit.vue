<template>
  <div class="col-md-10">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h2 class="panel-title">{{ T.userEmailEditEmail }}</h2>
      </div>
      <div class="panel-body">
        <form class="form-horizontal">
          <div class="form-group">
            <label class="col-md-3 control-label"
                 for="email">{{ T.userEmailEditEmail }}</label>
            <div class="col-md-7">
              <input class="form-control"
                   name='email'
                   size='30'
                   type='text'
                   v-model="email">
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-offset-3 col-md-7">
              <button class="btn btn-primary"
                   type="button"
                   v-on:click.prevent="change">{{ T.wordsSaveChanges }}</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import {T} from '../../omegaup.js';

export default {
  props: {
    initialEmail: String,
  },
  data: function() {
    return { T: T, email: this.initialEmail, }
  },
  methods: {change: function() { this.$emit('submit', this.email);}}
}

</script>
