<template>
  <div class="omegaup-course-problemlist panel">
    <div class="panel-heading">
      <form>
        <div class="row">
          <div class="form-group col-md-8">
            <label>
              {{ T.wordsAssignments }}
              <select class="form-control" v-model="assignment">
                <option v-for="a in assignments" v-bind:value="a">
                  {{ a.name }}
                </option>
              </select>
            </label>
          </div>
          <div class="form-group col-md-4 pull-right" v-show="assignment.alias">
            <label>
              &nbsp;
              <button class="form-control btn btn-primary" v-on:click.prevent="onShowForm">{{ T.courseEditAddProblems }}</button>
            </label>
          </div>
        </div>
      </form>
    </div>
    <div class="table-body" v-if="assignmentProblems.length == 0">
      <div class="empty-category">{{ T.courseAssignmentProblemsEmpty }}</div>
    </div>
    <table class="table table-striped" v-else>
      <thead>
        <th>{{ T.contestAddproblemProblemName }}</th>
        <th>{{ T.contestAddproblemProblemRemove }}</th>
      </thead>
      <tbody>
        <tr v-for="problem in assignmentProblems">
          <td>{{ problem.title }}</td>
          <td class="button-column"><a v-bind:title="T.courseAssignmentProblemRemove" v-on:click="onRemove(problem)"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></a></td>
        </tr>
      </tbody>
    </table>
    <div class="panel-footer" v-show="showForm">
      <form>
        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label>
                {{ T.wordsTopics }}
                <select class="form-control" v-model="topics" multiple>
                  <!-- TODO: How do we do this in general? -->
                  <option value="binary-search">{{ T.problemTopicBinarySearch }}</option>
                  <option value="graph-theory">{{ T.problemTopicGraphTheory }}</option>
                  <option value="sorting">{{ T.problemTopicSorting }}</option>
                </select>
              </label>
            </div>
            <div class="form-group">
              <label>
                {{ T.wordsLevels }}
                <select class="form-control" v-model="level">
                  <option value="intro">{{ T.problemLevelIntro }}</option>
                  <option value="easy">{{ T.problemLevelEasy }}</option>
                  <option value="medium">{{ T.problemLevelMedium }}</option>
                  <option value="hard">{{ T.problemLevelHard }}</option>
                </select>
              </label>
            </div>
          </div>
          <div class="col-md-8">
            <div class="row">
              <div class="form-group col-md-12">
                <label>
                  {{ T.wordsProblems }}
                  <select size="15" class="form-control" v-model="taggedProblemAlias">
                    <option v-for="problem in taggedProblems" v-bind:value="problem.alias">{{ problem.title }}</option>
                  </select>
                </label>
              </div>
            </div>
            <div class="row">
              <div class="form-group col-md-12">
                <label>
                  {{ T.wordsProblem }}
                  <input v-model="problemAlias" class="typeahead form-control problems-dropdown" autocomplete="off" />
                </label>
                <p class="help-block">{{ T.courseAddProblemsAssignmentsDesc }}</p>
              </div>
            </div>
            <div class="form-group pull-right">
              <button type="submit" class="btn btn-primary" v-on:click.prevent="onAddProblem" v-bind:disabled="problemAlias.length == 0">{{ T.courseAddProblemsAdd }}</button>
              <button type="reset" class="btn btn-secondary" v-on:click.prevent="onCancel">{{ T.wordsCancel }}</button>
            </div>
          </div>
        </div>
      </form>
    </div> <!-- panel-body -->
  </div> <!-- panel -->
</template>

<script>
import UI from "../../ui.js";

export default {
  props: {
    T: Object,
    assignments: Array,
    assignmentProblems: Array,
    taggedProblems: Array,
  },
  data: function() {
    return {
      assignment: {},
      showForm: false,
      level: 'intro',
      topics: [],
      taggedProblemAlias: '',
      problemAlias: '',
    };
  },
  computed: {
    tags: function() {
      var t = this.topics.slice();
      t.push(this.level);
      return t;
    },
  },
  mounted: function() {
    UI.problemTypeahead($('input.problems-dropdown', $(this.$el)));
  },
  methods: {
    onAddProblem: function() {
      this.$emit('add-problem', this.assignment, this.problemAlias);
    },
    onShowForm: function() {
      this.showForm = true;
      this.problemAlias = '';
      this.level = 'intro';
      this.topics = [];
    },
    onCancel: function() {
      this.showForm = false;
    },
    onRemove: function(problem) {
      this.$emit('remove', this.assignment, problem);
    },
  },
  watch: {
    assignment: function(val) {
      this.$emit('assignment', val);
    },
    taggedProblemAlias: function() {
      this.problemAlias = this.taggedProblemAlias;
    },
    tags: function() {
      this.$emit('tags', this.tags);
    },
  },
};
</script>

<style>
.omegaup-course-problemlist .form-group>label {
  width: 100%;
}
</style>
