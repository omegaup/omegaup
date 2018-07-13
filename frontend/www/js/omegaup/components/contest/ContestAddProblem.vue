<template>
  <div class="panel panel-primary">
    <div class="panel-body">
      <form class="form"
            v-on:submit.prevent="onSubmit">
        <div class="form-group">
          <label>{{T.wordsProblem}}</label> <omegaup-autocomplete v-bind:init=
          "el =&gt; UI.problemTypeahead(el)"
               v-model="alias"></omegaup-autocomplete>
        </div>
        <div class="form-group">
          <label>{{T.contestAddproblemProblemPoints}}</label> <input class="form-control"
               size="3"
               v-model="points">
        </div>
        <div class="form-group">
          <label>{{T.contestAddproblemContestOrder}}</label> <input class="form-control"
               size="2"
               v-model="order">
        </div>
        <div class="form-group">
          <button class="btn btn-primary"
               type="submit">{{T.wordsAddProblem}}</button>
        </div>
      </form>
    </div>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>{{T.contestAddproblemContestOrder}}</th>
          <th>{{T.contestAddproblemProblemName}}</th>
          <th>{{T.contestAddproblemProblemPoints}}</th>
          <th>{{T.contestAddproblemProblemRemove}}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="problem in problems">
          <td>{{problem.order}}</td>
          <td>
            <a v-bind:href="`/arena/problem/${problem.alias}/`">{{problem.alias}}</a>
          </td>
          <td>{{problem.points}}</td>
          <td><button class="close">&times;</button></td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import {T, UI} from '../../omegaup.js';
import Autocomplete from '../Autocomplete.vue';
export default {
  props: {problems: Array},
  data: function() {
    return { T: T, UI: UI, alias: "", points: 100, order: 1, }
  },
  methods: {onSubmit: function() { this.$parent.$emit('add-problem', this);}},
  components: {'omegaup-autocomplete': Autocomplete}
}
</script>
