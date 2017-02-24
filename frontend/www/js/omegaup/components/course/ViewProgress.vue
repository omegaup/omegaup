<template>
  <div class="panel">
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
                <th>{{ T.wordsNumHomeworks }}</th>
                <th>{{ T.wordsNumTests }}</th>
              </tr>
            </thead>
            <tr v-for="student in students">
              <td><a v-bind:href="'/profile/' + student.username + '/'">{{ student.name || student.username }}</a></td>
              <td>{{ student.count_homeworks_done }} / {{ totalHomeworks }}</td>
              <td>{{ student.count_tests_done }} / {{ totalTests }}</td>
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
    totalHomeworks: Number,
    totalTests: Number,
  },
  data: function() {
    return {
    };
  },
};
</script>
