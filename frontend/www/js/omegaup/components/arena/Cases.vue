<template>
  <tr>
    <td></td>
    <td class="text-center">{{ problem.name }}</td>
    <td class="text-center">{{ problem.verdict }}</td>
    <td class="score">{{ problem.contest_score ? problem.contest_score : problem.score }}</td>
    <td class="center"
        width="10">{{ problem.max_score ? '/':'' }}</td>
    <td>{{ problem.max_score ? problem.max_score:'' }}</td>
  </tr>
</template>

<script>
export default {
  props: {
    problem: Object,
    show: Boolean,
  },
}

</script>
