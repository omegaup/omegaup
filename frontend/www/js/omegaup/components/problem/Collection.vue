<template>
  <div>
    <div class="row mb-2">
      <div class="col-md-7">
        <h1 class="card-title">{{ T.collectionTitle }}</h1>
      </div>
      <div class="col-md-5 text-right align-self-end">
        <a href="/problem/" data-nav-problems-all>{{ T.navAllProblems }}</a>
      </div>
    </div>
    <div class="card panel panel-default">
      <div class="card-header panel-heading">
        <h5 class="card-title panel-title">
          {{ T.problemCollectionEducationLevel }}
        </h5>
      </div>
      <div class="card-body panel-body">
        <div class="container-fluid">
          <div class="row">
            <omegaup-problem-collection
              v-for="(collection, idx) in problemCount"
              :key="idx"
              :title="getname(collection.name)"
              :problem-count="collection.problems_per_tag"
              :href="'/problem/collection/' + collection.name + '/'"
            >
              <template #icon>
                <font-awesome-icon
                  :icon="['fas', getProblemLevelIcon(collection.name)]"
                ></font-awesome-icon>
              </template>
              <template #problems>
                {{ T.wordsProblems }}
              </template>
            </omegaup-problem-collection>
          </div>
        </div>
      </div>
    </div>
    <div class="card panel panel-default">
      <div class="card-header panel-heading">
        <h5 class="card-title panel-title">
          {{ T.problemCollectionOthers }}
        </h5>
      </div>
      <div class="card-body panel-body">
        <div class="container-fluid">
          <div class="row">
            <omegaup-problem-static-collection
              :href="'/problem/author/'"
              :title="T.problemCollectionAuthors"
            >
              <template #icon>
                <font-awesome-icon :icon="['fas', 'user']"></font-awesome-icon>
              </template>
            </omegaup-problem-static-collection>
            <omegaup-problem-static-collection
              :href="'/problem/random/'"
              :title="T.problemCollectionRandomProblem"
            >
              <template #icon>
                <font-awesome-icon
                  :icon="['fas', 'random']"
                ></font-awesome-icon>
              </template>
            </omegaup-problem-static-collection>
            <omegaup-problem-static-collection
              :href="'/problem/'"
              :title="T.problemCollectionSearchProblem"
            >
              <template #icon>
                <font-awesome-icon
                  :icon="['fas', 'search']"
                ></font-awesome-icon>
              </template>
            </omegaup-problem-static-collection>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import problem_Collection from './CollectionProblem.vue';
import problem_Static_Collection from './CollectionProblem.vue';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
  faRobot,
  faLaptopCode,
  faSquareRootAlt,
  faProjectDiagram,
  faSitemap,
  faTrophy,
  faCode,
  faUsers,
  faRandom,
  faSearch,
} from '@fortawesome/free-solid-svg-icons';
library.add(faRobot);
library.add(faLaptopCode);
library.add(faSquareRootAlt);
library.add(faProjectDiagram);
library.add(faSitemap);
library.add(faTrophy);
library.add(faCode);
library.add(faUsers);
library.add(faRandom);
library.add(faSearch);

const problemLevelIcons: { [key: string]: string } = {
  problemLevelBasicKarel: 'robot',
  problemLevelBasicIntroductionToProgramming: 'laptop-code',
  problemLevelIntermediateMathsInProgramming: 'square-root-alt',
  problemLevelIntermediateDataStructuresAndAlgorithms: 'project-diagram',
  problemLevelIntermediateAnalysisAndDesignOfAlgorithms: 'sitemap',
  problemLevelAdvancedCompetitiveProgramming: 'trophy',
  problemLevelAdvancedSpecializedTopics: 'code',
  problemCollectionAuthors: 'users',
  problemCollectionRandomProblem: 'random',
  problemCollectionSearchProblem: 'search',
};

@Component({
  components: {
    'omegaup-problem-collection': problem_Collection,
    'omegaup-problem-static-collection': problem_Static_Collection,
    FontAwesomeIcon,
  },
})
export default class Collection extends Vue {
  @Prop() levelTags!: string[];
  @Prop() problemCount!: string[];
  T = T;

  getProblemLevelIcon(problemLevel: string): string {
    if (Object.prototype.hasOwnProperty.call(problemLevelIcons, problemLevel))
      return problemLevelIcons[problemLevel];
    return 'icon';
  }

  getname(alias: string): string {
    return T[alias];
  }
}
</script>
