<template>
  <div class="omegaup-course-viewstudent panel">
    <div class="page-header">
      <h2><a v-bind:href="courseUrl">{{ course.name }}</a></h2>
    </div>
    <div class="panel-body">
      <form>
        <select v-model="selectedStudent">
          <option v-bind:value="student"
                  v-for="student in students">
            {{ student.name || student.username }}
          </option>
        </select>
      </form>
      <hr>
      <form>
        <select v-model="selectedAssignment">
          <option v-bind:value="assignment"
                  v-for="assignment in assignments">
            {{ assignment.name }}
          </option>
        </select>
      </form>
      <div v-if="selectedAssignment">
        <p class="assignment-description"
           v-text="selectedAssignment.description"></p>
        <hr>
        <div>
          <ul class="nav nav-tabs"
              role="tablist">
            <li role="presentation"
                v-bind:class="{ active: problem == selectedProblem }"
                v-for="problem in problems">
              <a aria-controls="home"
                  data-toggle="tab"
                  href="#home"
                  role="tab"
                  v-on:click="selectedProblem = problem">
              <template v-if="problem.runs.length &gt; 0">
                {{ bestScore(problem) * problem.points }} / {{ problem.points }} -
              </template>{{ problem.title }} ({{ problem.runs.length }})</a>
            </li>
          </ul>
          <div v-if="!selectedProblem || selectedProblem.runs.length == 0">
            <div class="empty-category">
              {{ T.courseAssignmentProblemRunsEmpty }}
            </div>
          </div>
          <div class="panel"
               v-else="">
            <div class="panel-header">
              <pre>{{ bestRunSource(selectedProblem) }}</pre>
            </div>
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>{{ T.wordsTime }}</th>
                  <th>{{ T.wordsStatus }}</th>
                  <th class="numeric">{{ T.wordsPercentage }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="run in selectedProblem.runs">
                  <td>{{ formatDateTime(run.time) }}</td>
                  <td>{{ run.verdict }}</td>
                  <td class="numeric">{{ 100 * run.score }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div><!-- panel-body -->
  </div><!-- panel -->
</template>

<script>
import UI from '../../ui.js';

export default {
  props: {
    T: Object,
    assignments: Array,
    course: Object,
    initialStudent: Object,
    problems: Array,
    students: Array,
  },
  data: function() {
    return {
      selectedAssignment: null,
      selectedProblem: null,
      selectedStudent: this.initialStudent,
    };
  },
  methods: {
    bestRun: function(problem) {
      var best = null;
      for (let run of problem.runs) {
        if (!best || best.score < run.score ||
            best.score == run.score && best.penalty > run.penalty) {
          best = run;
        }
      }
      return best;
    },
    bestRunSource: function(problem) {
      let best = this.bestRun(problem);
      return (best && best.source) || '';
    },
    bestScore: function(problem) {
      let best = this.bestRun(problem);
      return (best && best.score) || 0.0;
    },
    formatDateTime: function(date) { return UI.formatDateTime(date);},
    score: function(student, assignment) {
      let score = student.progress[assignment.alias] || '0';
      return parseFloat(score).toPrecision(2);
    },
  },
  computed: {
    courseUrl: function() { return '/course/' + this.course.alias + '/';},
  },
  mounted: function() {
    let self = this;
    window.addEventListener('popstate', function(ev) {
      self.selectedStudent =
          (ev.state && ev.state.student) || self.initialStudent;
    });
  },
  watch: {
    selectedStudent: function(student, oldStudent) {
      this.$emit('update', this.selectedStudent, this.selectedAssignment);
      if (student && oldStudent && student.username == oldStudent.username) {
        return;
      }
      window.history.pushState({student: student}, document.title,
                               '/course/' + this.course.alias + '/student/' +
                                   student.username + '/');
    },
    selectedAssignment: function(assignment) {
      this.$emit('update', this.selectedStudent, this.selectedAssignment);
    },
    problems: function(problems) {
      if (problems.length == 0) {
        this.selectedProblem = null;
        return;
      }
      this.selectedProblem = problems[0];
    },
  },
};
</script>

<style>
.omegaup-course-viewstudent p.assignment-description {
  padding: 1em;
}
</style>
