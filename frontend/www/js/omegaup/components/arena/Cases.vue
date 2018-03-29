<template>
  <tr>
    <td></td>
    <td class="case__data">{{ problem.name }}</td>
    <td class="case__data">{{ problem.verdict }}</td>
    <td class="score">{{ problem.contest_score ? problem.contest_score : problem.score }}</td>
    <td class="center" width="10">{{ problem.max_score ? '/':'' }}</td>
    <td>{{ problem.max_score ? problem.max_score:'' }}</td>
</template>
<script>
  export default {
    name: 'Cases',
    props: {
      problem: Object,
      show: Boolean,
    },
  }
</script>
<style>
  td.case__data {
    text-align: center;
  }
</style>