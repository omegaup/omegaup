<template>
  <b-container fluid>
    <b-tabs content-class="mt-3" align="center">
      <b-tab :title="T.wordsProblem" active>
        <h3 :data-problem-title="problem.alias" class="text-center mb-4">
      {{ title }}
          <template v-if="showVisibilityIndicators">
            <img
              v-if="problem.quality_seal || problem.visibility === 3"
              src="/media/quality-badge-sm.png"
              :title="T.wordsHighQualityProblem"
              class="mr-2"
            />
            <font-awesome-icon
              v-if="problem.visibility === 1 || problem.visibility === -1"
              :icon="['fas', 'exclamation-triangle']"
              :title="T.wordsWarningProblem"
              class="mr-2"
            ></font-awesome-icon>
            <font-awesome-icon
              v-if="problem.visibility === 0 || problem.visibility === -1"
              :icon="['fas', 'eye-slash']"
              :title="T.wordsPrivate"
              class="mr-2"
            ></font-awesome-icon>
            <font-awesome-icon
              v-if="problem.visibility <= -2"
              :icon="['fas', 'ban']"
              :title="T.wordsBannedProblem"
              class="mr-2"
              color="darkred"
            ></font-awesome-icon>
          </template>

          <a v-if="showEditLink" :href="`/problem/${problem.alias}/edit/`">
            <font-awesome-icon :icon="['fas', 'edit']" />
          </a>
        </h3>
      </b-tab>
      <b-tab :title="T.wordsRuns">
        <p>I'm the second tab</p>
      </b-tab>
      <b-tab :title="T.wordsClarifications">
        <p>I'm the third tab</p>
      </b-tab>
      <b-tab :title="T.wordsFeedback">
        <p>I'm the fourth tab</p>
      </b-tab>
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
  @Prop() currentProblem!: types.ArenaCourseCurrentProblem;

  T = T;
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
</style>
