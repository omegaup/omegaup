<template>
                     v-show="show"></omegaup-arena-cases>
<body>
  <tbody>
    <tr class="group">
      <th class="center">{{ groupElement.group }}</th>
      <th colspan="2">
        <div class="dropdown-cases"
             v-on:click="toggle">
          <span v-bind:class=
          "{'glyphicon glyphicon-collapse-down': !show, 'glyphicon glyphicon-collapse-up': show}"></span>
        </div>
      </th>
      <th class="score">{{ groupElement.contest_score ? groupElement.contest_score :
      groupElement.score}}</th>
      <th class="center"
          width="10">{{ groupElement.max_score ? '/':''}}</th>
      <th>{{ groupElement.max_score ? groupElement.max_score : '' }}</th>
    </tr>
  </tbody>
</template>

<script>
import arena_Cases from './Cases.vue';
export default {
  props: {
    groupElement: Object,
  },
  methods: {
    toggle() { this.show = !this.show;},
  },
  data: function() {
    return { show: false, }
  },
  components: {
    'omegaup-arena-cases': arena_Cases,
  },
}

</script>

<style>
  .dropdown-cases {
    height: 100%;
    width: 100%;
    margin: 0 auto;
    text-align: center;
    background: rgb(245, 245, 245);
    border-radius: 5px;
  }
</style>
