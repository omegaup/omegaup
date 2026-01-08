<template>
  <div>
    <div class="row mb-2 justify-content-center">
      <h2 class="text-center mb-4 col-md-7">{{ T.collectionTitle }}</h2>
    </div>
    <div class="card panel panel-default">
      <div
        class="card-header panel-heading d-flex justify-content-between align-items-center"
      >
        <h5 class="card-title panel-title mb-0">
          {{ T.problemCollectionEducationLevel }}
        </h5>
        <a class="btn btn-primary" href="/problem/" data-nav-problems-all>{{
          T.navAllProblems
        }}</a>
      </div>
      <div class="card-body panel-body">
        <div class="container-fluid px-0">
          <div class="row d-flex justify-content-center">
            <omegaup-problem-collection
              v-for="(collection, idx) in problemCount"
              :key="idx"
              :title="getName(collection.name)"
              class="educational-level-card"
            >
              <template #icon>
                <font-awesome-icon
                  :icon="['fas', getProblemLevelIcon(collection.name)]"
                ></font-awesome-icon>
              </template>
              <template #problem-count>
                <p class="card-text">
                  {{
                    ui.formatString(T.problemCollectionProblemCount, {
                      count: collection.problems_per_tag,
                    })
                  }}
                </p>
              </template>
              <template #button>
                <a
                  class="btn btn-primary"
                  :class="{ disabled: collection.problems_per_tag == 0 }"
                  :href="`/problem/collection/${encodeURIComponent(
                    collection.name,
                  )}/`"
                  >{{ T.problemcollectionViewProblems }}</a
                >
              </template>
            </omegaup-problem-collection>
          </div>
        </div>
      </div>
    </div>
    <div class="card mt-4 panel panel-default">
      <div class="card-header panel-heading">
        <h5 class="card-title panel-title mb-0">
          {{ T.problemCollectionOthers }}
        </h5>
      </div>
      <div class="card-body panel-body">
        <div class="container-fluid">
          <div class="row d-flex justify-content-center">
            <omegaup-problem-collection :title="T.problemCollectionAuthors">
              <template #icon>
                <font-awesome-icon class="mt-3" :icon="['fas', 'users']">
                </font-awesome-icon>
              </template>
              <template #button>
                <a class="btn btn-primary" href="/problem/collection/author/">{{
                  T.problemcollectionViewProblems
                }}</a>
              </template>
            </omegaup-problem-collection>
            <omegaup-problem-collection
              :title="T.problemCollectionRandomLanguageProblem"
            >
              <template #icon>
                <font-awesome-icon class="mt-3" :icon="['fas', 'cogs']" />
              </template>
              <template #button>
                <a class="btn btn-primary" href="/problem/random/language/">{{
                  T.problemcollectionViewProblems
                }}</a>
              </template>
            </omegaup-problem-collection>
            <omegaup-problem-collection
              :title="T.problemCollectionRandomKarelProblem"
            >
              <template #icon>
                <font-awesome-icon
                  class="mt-3"
                  :icon="['fas', 'random']"
                ></font-awesome-icon>
              </template>
              <template #button>
                <a class="btn btn-primary" href="/problem/random/karel/">{{
                  T.problemcollectionViewProblems
                }}</a>
              </template>
            </omegaup-problem-collection>
            <omegaup-problem-collection
              :title="T.problemCollectionSearchProblem"
            >
              <template #icon>
                <font-awesome-icon
                  class="mt-3"
                  :icon="['fas', 'search']"
                ></font-awesome-icon>
              </template>
              <template #button>
                <button
                  class="btn btn-primary"
                  @click="showFinderWizard = true"
                >
                  {{ T.wordsSearch }}
                </button>
              </template>
            </omegaup-problem-collection>
            <!-- TODO: Migrar el problem finder a BS4 (solo para eliminar algunos estilos) -->
            <omegaup-problem-finder-wizard
              v-show="showFinderWizard"
              :possible-tags="allTags"
              @close="showFinderWizard = false"
              @search-problems="$emit('search-problems', $event)"
            ></omegaup-problem-finder-wizard>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';
import * as ui from '../../ui';
import problem_Collection from './CollectionProblem.vue';
import problem_FinderWizard from './FinderWizard.vue';

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
  faCogs,
} from '@fortawesome/free-solid-svg-icons';
(library as any).add(faRobot);
(library as any).add(faLaptopCode);
(library as any).add(faSquareRootAlt);
(library as any).add(faProjectDiagram);
(library as any).add(faSitemap);
(library as any).add(faTrophy);
(library as any).add(faCode);
(library as any).add(faUsers);
(library as any).add(faRandom);
(library as any).add(faSearch);
(library as any).add(faCogs);

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
    FontAwesomeIcon,
    'omegaup-problem-finder-wizard': problem_FinderWizard,
  },
})
export default class Collection extends Vue {
  @Prop() levelTags!: string[];
  @Prop() problemCount!: { name: string; problems_per_tag: number }[];
  @Prop() allTags!: types.Tag[];
  T = T;
  ui = ui;
  showFinderWizard = false;

  getProblemLevelIcon(problemLevel: string): string {
    if (Object.prototype.hasOwnProperty.call(problemLevelIcons, problemLevel))
      return problemLevelIcons[problemLevel];
    return 'icon';
  }

  getName(alias: string): string {
    return T[alias];
  }
}
</script>

<style>
.educational-level-card[omegaup-collection-problem] {
  width: 12.5rem !important;
  height: 12.5rem !important;
  position: relative;
  transition: box-shadow 0.3s ease-in-out !important;
}

.educational-level-card[omegaup-collection-problem]:hover {
  box-shadow: 0 0 0.625rem rgba(103, 141, 215, 0.5) !important;
}

/* Apply same hover effect to other collection cards */
[omegaup-collection-problem] {
  transition: box-shadow 0.3s ease-in-out !important;
}

[omegaup-collection-problem]:hover {
  box-shadow: 0 0 0.625rem rgba(103, 141, 215, 0.5) !important;
}

.educational-level-card[omegaup-collection-problem] .card-body {
  min-height: 12.5rem !important;
  height: 12.5rem !important;
  display: flex !important;
  flex-direction: column !important;
  padding: 1rem !important;
}

/* Adjust spacing for problem count to not overlap with button */
.educational-level-card[omegaup-collection-problem] .card-text {
  margin-bottom: 0 !important;
}

.educational-level-card[omegaup-collection-problem] .mt-1 {
  margin-top: auto !important;
}
</style>
