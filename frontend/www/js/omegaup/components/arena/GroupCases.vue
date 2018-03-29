<template>
    <tbody>
      <tr class="group">
        <th class="center">{{ groupElement.group }}</th>
        <th colspan="2">
          <div @click="click" class="dropdown_cases">
            <span :class="{'glyphicon glyphicon-collapse-down': !show, 'glyphicon glyphicon-collapse-up': show}"></span>
          </div>
        </th>
        <th class="score">{{ groupElement.contest_score ? groupElement.contest_score : groupElement.score}}</th>
        <th class="center" width="10">{{ groupElement.max_score ? '/':''}}</th>
        <th>{{ groupElement.max_score ? groupElement.max_score : '' }}</th>
      </tr>
      <cases v-show="show" v-for="problemCase in groupElement.cases" :problem=problemCase></cases>
    </tbody>
</template>
<script>
  import Cases from './Cases.vue'
  export default {
    name: 'GroupCases',
    props: {
      groupElement: Object,
      T: Object,
    },
    methods: {
      click() {
        this.show = !this.show;
      },
    },
    data: function() {
      return {show: false,}
    },
    components: {
      Cases,
    },
  }
</script>

<style>
  .dropdown_cases {
    height: 100%;
    width: 100%;
    margin: 0 auto;
    text-align: center;
    background: rgb(245, 245, 245);
    border-radius: 5px;
  }
</style>

