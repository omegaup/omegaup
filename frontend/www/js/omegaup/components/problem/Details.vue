<template>
  <div>
    <ul class="nav justify-content-center nav-tabs" role="tablist">
      <li class="nav-item" v-for="tab in availableTabs">
        <a
          href="#"
          class="nav-link"
          data-toggle="tab"
          role="tab"
          v-bind:aria-controls="tab.name"
          v-bind:class="{ active: selectedTab === tab.name }"
          v-bind:aria-selected="selectedTab === tab.name"
          v-on:click="selectedTab = tab.name"
        >
          {{ tab.text }}
        </a>
      </li>
    </ul>
    <div class="tab-content">
      <div
        class="tab-pane fade p-4"
        v-bind:class="{ 'show active': selectedTab === 'problems' }"
      >
        <h3 class="text-center mb-4">
          {{ problem.title }}
          <img
            src="/media/quality-badge-sm.png"
            v-bind:title="T.wordsHighQualityProblem"
            v-if="problem.quality_seal || problem.visibility === 3"
          />
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
        <table class="table table-bordered mx-auto w-75 mb-0">
          <tr>
            <td>{{ T.wordsPoints }}</td>
            <td>{{ problem.points }}</td>
            <td>{{ T.wordsMemoryLimit }}</td>
            <td>{{ problem.limits.memory_limit }}</td>
          </tr>
          <tr>
            <td>{{ T.wordsTimeLimit }}</td>
            <td>{{ problem.limits.time_limit }}</td>
            <td>{{ T.wordsOverallWallTimeLimit }}</td>
            <td>{{ problem.limits.overall_wall_time_limit }}</td>
          </tr>
          <tr>
            <td>{{ T.problemEditFormInputLimit }}</td>
            <td>{{ problem.limits.input_limit }}</td>
          </tr>
        </table>

        <div class="karel-js-link my-3" v-if="problem.karel_problem">
          <a
            class="p-3"
            v-bind:href="
              `/karel.js/${
                problem.sample_input ? `#mundo:${problem.sample_input}` : ''
              }`
            "
            target="_blank"
          >
            {{ T.openInKarelJs }}
            <font-awesome-icon v-bind:icon="['fas', 'external-link-alt']" />
          </a>
        </div>
        <div class="mt-4 markdown">
          <vue-mathjax
            v-on:change="hasChanged"
            v-bind:formula="problemStatement"
            v-bind:safe="false"
          ></vue-mathjax>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.karel-js-link {
  border: 1px solid #eee;
  border-left: 0;
  border-radius: 3px;

  a {
    border-left: 5px solid #1b809e;
    display: block;
  }
}
</style>

<script lang="ts">
import { Vue, Component, Prop, Emit } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as markdown from '../../markdown';

import { VueMathjax } from 'vue-mathjax';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
  faExclamationTriangle,
  faEyeSlash,
  faBan,
  faExternalLinkAlt,
} from '@fortawesome/free-solid-svg-icons';
library.add(faExclamationTriangle, faEyeSlash, faBan, faExternalLinkAlt);

interface Tab {
  name: string;
  text: string;
}

@Component({
  components: {
    FontAwesomeIcon,
    'vue-mathjax': VueMathjax,
  },
})
export default class ProblemDetails extends Vue {
  @Prop() problem!: types.ProblemInfo;

  T = T;
  selectedTab = 'problems';
  markdownConverter = markdown.markdownConverter();

  get availableTabs(): Tab[] {
    let tabs = [
      {
        name: 'problems',
        text: T.wordsProblem,
      },
    ];
    return tabs;
  }

  get problemStatement(): string {
    return this.markdownConverter.makeHtmlWithImages(
      this.problem.statement.markdown,
      this.problem.statement.images,
      this.problem.settings,
    );
  }
}
</script>
