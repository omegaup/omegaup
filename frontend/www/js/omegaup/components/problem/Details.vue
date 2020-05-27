<template>
  <div>
    <ul class="nav justify-content-center nav-tabs" role="tablist">
      <li class="nav-item"
        v-for="tab in availableTabs"
      >
        <a
          href="#"
          class="nav-link"
          data-toggle="tab"
          role="tab"
          v-bind:aria-controls="tab.name"
          v-bind:class="{ active: selectedTab === tab.name}"
          v-bind:aria-selected="selectedTab === tab.name"
          v-on:click="selectedTab = tab.name"
        >
          {{ tab.text }}
        </a>
      </li>
    </ul>
    <div class="tab-content">
      <div class="tab-pane fade p-4" v-bind:class="{ 'show active': selectedTab === 'problems' }">
        <h3 class="text-center">
          {{ problem.title }}
          <img
            src="/media/quality-badge-sm.png"
            v-bind:title="T.wordsHighQualityProblem"
            v-if="problem.quality_seal || problem.visibility === 3"
          ></img>
          <font-awesome-icon
            v-if="problem.visibility === 1 || problem.visibility === -1"
            v-bind:icon="['fas', 'exclamation-triangle']"
            v-bind:tiitle="T.wordsWarningProblem"
          />
          <font-awesome-icon
            v-if="problem.visibility === 0 || problem.visibility === -1"
            v-bind:icon="['fas', 'eye-slash']"
            v-bind:title="T.wordsPrivate"
          />
          <font-awesome-icon
            v-if="problem.visibility <= -2"
            v-bind:icon="['fas', 'ban']"
            v-bind:title="T.wordsBannedProblem"
            color="darkred"
          />
          <!-- TODO: Add link to EditProblem if user is problem admin -->
        </h3>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faExclamationTriangle, faEyeSlash, faBan } from '@fortawesome/free-solid-svg-icons';
library.add(
  faExclamationTriangle,
  faEyeSlash,
  faBan
);

interface Tab {
  name: string;
  text: string;
}

@Component({
  components: {
    FontAwesomeIcon,
  }
})
export default class ProblemDetails extends Vue {
  @Prop() problem!: types.ProblemInfo;

  T = T;
  selectedTab = 'problems';

  get availableTabs(): Tab[] {
    let tabs = [{
      name: 'problems',
      text: T.wordsProblem,
    }];

    return tabs;
  }
}
</script>