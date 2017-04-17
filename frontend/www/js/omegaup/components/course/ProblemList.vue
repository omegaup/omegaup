<template>
  <div class="omegaup-course-problemlist panel">
    <div class="panel-heading">
      <form>
        <div class="row">
          <div class="form-group col-md-8">
            <label>{{ T.wordsAssignments }} <select class="form-control"
                    v-model="assignment">
              <option v-bind:value="a"
                      v-for="a in assignments">
                {{ a.name }}
              </option>
            </select></label>
          </div>
          <div class="form-group col-md-4 pull-right"
               v-show="assignment.alias">
            <label>&nbsp; <button class="form-control btn btn-primary"
                    v-on:click.prevent="onShowForm">{{ T.courseEditAddProblems }}</button></label>
          </div>
        </div>
      </form>
    </div>
    <div class="table-body"
         v-if="assignmentProblems.length == 0">
      <div class="empty-category">
        {{ T.courseAssignmentProblemsEmpty }}
      </div>
    </div>
    <table class="table table-striped"
           v-else="">
      <thead>
        <tr>
          <th>{{ T.contestAddproblemProblemName }}</th>
          <th>{{ T.contestAddproblemProblemRemove }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="problem in assignmentProblems">
          <td>{{ problem.title }}</td>
          <td class="button-column">
            <a v-bind:title="T.courseAssignmentProblemRemove"
                v-on:click="onRemove(problem)"><span aria-hidden="true"
                  class="glyphicon glyphicon-remove"></span></a>
          </td>
        </tr>
      </tbody>
    </table>
    <div class="panel-footer"
         v-show="showForm">
      <form>
        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label>{{ T.wordsTopics }} <select class="form-control"
                      multiple
                      v-model="topics">
                <!-- TODO: How do we do this in general? -->
                <option value="binary-search">
                  {{ T.problemTopicBinarySearch }}
                </option>
                <option value="graph-theory">
                  {{ T.problemTopicGraphTheory }}
                </option>
                <option value="sorting">
                  {{ T.problemTopicSorting }}
                </option>
              </select></label>
            </div>
            <div class="form-group">
              <label>{{ T.wordsLevels }} <select class="form-control"
                      v-model="level">
                <option value="intro">
                  {{ T.problemLevelIntro }}
                </option>
                <option value="easy">
                  {{ T.problemLevelEasy }}
                </option>
                <option value="medium">
                  {{ T.problemLevelMedium }}
                </option>
                <option value="hard">
                  {{ T.problemLevelHard }}
                </option>
              </select></label>
            </div>
          </div>
          <div class="col-md-8">
            <div class="row">
              <div class="form-group col-md-12">
                <label>{{ T.wordsProblems }} <select class="form-control"
                        size="15"
                        v-model="taggedProblemAlias">
                  <option v-bind:value="problem.alias"
                          v-for="problem in taggedProblems">
                    {{ problem.title }}
                  </option>
                </select></label>
              </div>
            </div>
            <div class="row">
              <div class="form-group col-md-12">
                <label>{{ T.wordsProblem }} <input autocomplete="off"
                       class="typeahead form-control problems-dropdown"
                       v-model="problemAlias"></label>
                <p class="help-block">{{ T.courseAddProblemsAssignmentsDesc }}</p>
              </div>
            </div>
            <div class="form-group pull-right">
              <button class="btn btn-primary"
                   type="submit"
                   v-bind:disabled="problemAlias.length == 0"
                   v-on:click.prevent="onAddProblem">{{ T.courseAddProblemsAdd }}</button>
                   <button class="btn btn-secondary"
                   type="reset"
                   v-on:click.prevent="onCancel">{{ T.wordsCancel }}</button>
            </div>
          </div>
        </div>
      </form>
    </div><!-- panel-body -->
  </div><!-- panel -->
</template>

<script>
import UI from '../../ui.js';

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
    var self = this;
    UI.problemTypeahead(
        $('input.problems-dropdown', $(this.$el)),
        function(event, item) { self.problemAlias = item.alias; });
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
    onCancel: function() { this.showForm = false;},
    onRemove: function(problem) {
      this.$emit('remove', this.assignment, problem);
    },
  },
  watch: {
    assignment: function(val) { this.$emit('assignment', val);},
    taggedProblemAlias: function() {
      this.problemAlias = this.taggedProblemAlias;
    },
    tags: function() { this.$emit('tags', this.tags);},
  },
};

</script>

<style>
.omegaup-course-problemlist .form-group>label {
  width: 100%;
}
</style>
