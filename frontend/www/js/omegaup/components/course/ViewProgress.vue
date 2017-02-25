<template>
  <div class="omegaup-course-viewprogress panel">
    <div class="page-header">
      <h2>{{ T.courseStudentsProgress }}</h2>
    </div>
    <div class="panel-body">
      <div class="panel">
        <div class="panel-body">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>{{ T.wordsName }}</th>
                <th v-for="assignment in assignments">{{ assignment.name }}</th>
              </tr>
            </thead>
            <tr v-for="student in students">
              <td><a v-bind:href="'/profile/' + student.username + '/'">{{ student.name || student.username }}</a></td>
              <td class="score" v-for="assignment in assignments"> {{ (assignment.alias in student.progress) ? student.progress[assignment.alias] : 0 }}</td>
            </tr>
          </table>
        </div>
      </div>
    </div> <!-- panel-body -->
  </div> <!-- panel -->
</template>

<script>
export default {
  props: {
    T: Object,
    students: Array,
    assignments: Array,
  },
  data: function() {
    return {
    };
  },
};
</script>

<style>
.omegaup-course-viewprogress td, .omegaup-course-viewprogress th {
  max-width: 0;
  text-overflow: ellipsis;
  overflow: hidden;
}
.omegaup-course-viewprogress .score {
  text-align: center;
}
</style>