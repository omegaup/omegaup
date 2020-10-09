<template>
  <div class="col-sm-4 mt-2">
    <div class="border border-dark">
      <div class="row">
        <div class="col-sm-4 d-flex align-items-center justify-content-end">
          <h1>
            <font-awesome-icon
              :icon="['fas', getProblemLevelIcon(levelTagAlias)]"
            />
          </h1>
        </div>
        <div class="col-sm-8 d-flex align-items-center" style="height: 112px">
          <p>
            <strong>{{ name }}</strong> <br />
            {{ problemCount }} <slot name="problems"></slot>
          </p>
        </div>
      </div>
      <div class="row">
        <div class="col mt-1 mb-1 text-center">
          <a class="btn btn-primary" :href="getProblemLink(levelTagAlias)">{{
            T.problemcollectionViewProblems
          }}</a>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import 'v-tooltip/dist/v-tooltip.css';
import { VTooltip } from 'v-tooltip';

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
  directives: {
    tooltip: VTooltip,
  },
  components: {
    FontAwesomeIcon,
  },
})
export default class CollectionProblem extends Vue {
  @Prop() levelTagAlias!: string;
  @Prop() problemCount!: number;
  T = T;

  get name(): string {
    return T[this.levelTagAlias];
  }

  getProblemLevelIcon(problemLevel: string): string {
    if (Object.prototype.hasOwnProperty.call(problemLevelIcons, problemLevel))
      return problemLevelIcons[problemLevel];
    return 'icon';
  }

  getProblemLink(alias: string): string {
    if (
      alias !== 'problemCollectionAuthors' &&
      alias !== 'problemCollectionRandomProblem' &&
      alias !== 'problemCollectionSearchProblem'
    )
      return '/problem/collection/' + alias + '/';
    if (alias === 'problemCollectionAuthors') return '/problem/';
    if (alias === 'problemCollectionRandomProblem') return '/problem/';
    if (alias === 'problemCollectionSearchProblem') return '/problem/';
    return '/problem/';
  }
}
</script>
