<template>
  <b-container fluid>
    <b-tabs content-class="mt-3" align="center">
      <b-tab :title="T.wordsProblem" active>a</b-tab>
      <b-tab :title="T.wordsRuns">a</b-tab>
      <b-tab :title="T.wordsClarifications">a</b-tab>
    </b-tabs>
  </b-container>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';

import { BootstrapVue } from 'bootstrap-vue';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
Vue.use(BootstrapVue);

export interface Tab {
  name: string;
  text: string;
}

@Component
export default class ProblemDetails extends Vue {
  @Prop() problem!: types.ProblemDetails;

  T = T;
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
</style>
