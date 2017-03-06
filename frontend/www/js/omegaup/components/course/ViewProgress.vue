<template>
  <div class="omegaup-course-viewprogress panel">
    <div class="page-header">
      <h2><a v-bind:href="courseUrl">{{ course.name }}</a></h2>
    </div>
    <div class="panel-body">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>{{ T.wordsName }}</th>
            <th class="score" v-for="assignment in assignments">{{ assignment.name }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="student in students">
            <td><a v-bind:href="'/profile/' + student.username + '/'">{{ student.name || student.username }}</a></td>
            <td class="score" v-for="assignment in assignments">{{ score(student, assignment) }}</td>
          </tr>
        </tbody>
      </table>
    </div> <!-- panel-body -->
    <div class="panel-footer">
      <a v-bind:href="csvDataUrl" v-bind:download="csvFilename">{{ T.courseStudentsProgressExportToSpreadsheet }}</a>
    </div>
  </div> <!-- panel -->
</template>

<script>
function escapeCsv(cell) {
  if (typeof(cell) === 'undefined' || typeof(cell) === 'null') {
    return '';
  }
  if (typeof(cell) !== 'string') {
    cell = JSON.stringify(cell);
  }
  if (cell.indexOf(',') === -1 && cell.indexOf('"') === -1 &&
      cell.indexOf("'") === -1) {
    return cell;
  }
  return '"' + cell.replace('"', '""') + '"';
}

function toCsv(table) {
  return table.map((row) => row.map(escapeCsv).join(',')).join('\r\n');
}

export default {
  props: {
    T: Object,
    course: Object,
    students: Array,
    assignments: Array,
  },
  data: function() {
    return {
    };
  },
  methods: {
    score: function(student, assignment) {
      let score = student.progress[assignment.alias] || '0';
      return parseFloat(score).toPrecision(2);
    },
  },
  computed: {
    courseUrl: function() {
      return '/course/' + this.course.alias + '/';
    },
    csvFilename: function() {
      return this.course.alias + '.csv';
    },
    csvDataUrl: function() {
      let table = [];
      let header = [this.T.profileUsername, this.T.wordsName];
      for (let assignment of this.assignments) {
        header.push(assignment.name);
      }
      table.push(header);
      for (let student of this.students) {
        let row = [student.username, student.name];
        for (let assignment of this.assignments) {
          row.push(this.score(student, assignment));
        }
        table.push(row);
      }
      let blob = new Blob([toCsv(table)], { type: 'text/csv;charset=utf-8;' });
      return window.URL.createObjectURL(blob);
    },
  },
};
</script>

<style>
.omegaup-course-viewprogress td, .omegaup-course-viewprogress th {
  /* max-width 0 makes cell width proportional and allows content to overflow */
  max-width: 0;
  text-overflow: ellipsis;
  overflow: hidden;
}
.omegaup-course-viewprogress .score {
  text-align: right;
}
</style>
