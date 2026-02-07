<template>
  <div class="container py-4">
    <div class="card mb-4">
      <div class="card-body">
        <h1 class="mb-3">{{ T.omegaupTitleHelp }}</h1>
        <p class="text-muted">{{ T.helpWelcomeMessage }}</p>
      </div>
    </div>

    <div class="row">
      <div
        v-for="resource in helpResources"
        :key="resource.name"
        class="col-md-4 mb-4"
      >
        <div class="card h-100">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title">{{ getResourceLabel(resource.name) }}</h5>
            <p class="card-text text-muted flex-grow-1">
              {{ getResourceDescription(resource.name) }}
            </p>
            <a
              :href="resource.url"
              :target="resource.external ? '_blank' : '_self'"
              :rel="resource.external ? 'noopener noreferrer' : ''"
              class="btn btn-primary"
            >
              {{ T.helpVisit }}
              <font-awesome-icon
                v-if="resource.external"
                :icon="['fas', 'external-link-alt']"
                class="ml-1"
                size="xs"
              />
            </a>
          </div>
        </div>
      </div>
    </div>

    <hr class="my-4" />

    <h4>{{ T.helpAdditionalResources }}</h4>
    <ul class="list-unstyled">
      <li class="mb-2">
        <a href="/privacypolicy/">{{ T.wordsPrivacyPolicy }}</a>
      </li>
      <li class="mb-2">
        <a href="/docs/">{{ T.helpDocumentation }}</a>
      </li>
    </ul>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';
import {
  FontAwesomeIcon,
  FontAwesomeLayers,
  FontAwesomeLayersText,
} from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
library.add(fas);

@Component({
  components: {
    'font-awesome-icon': FontAwesomeIcon,
    'font-awesome-layers': FontAwesomeLayers,
    'font-awesome-layers-text': FontAwesomeLayersText,
  },
})
export default class CommonHelp extends Vue {
  @Prop() helpResources!: types.HelpResource[];

  T = T;

  getResourceLabel(name: string): string {
    const labelMap: { [key: string]: string } = {
      tutorials: T.helpTutorials,
      discord: T.helpDiscord,
      omegaUpBlog: T.helpBlog,
      algorithmsBook: T.helpAlgorithmsBook,
      documentation: T.helpDocumentation,
      github: T.helpGitHub,
    };
    return labelMap[name] || name;
  }

  getResourceDescription(name: string): string {
    const descMap: { [key: string]: string } = {
      tutorials: T.helpTutorialsDesc,
      discord: T.helpDiscordDesc,
      omegaUpBlog: T.helpBlogDesc,
      algorithmsBook: T.helpAlgorithmsBookDesc,
      documentation: T.helpDocumentationDesc,
      github: T.helpGitHubDesc,
    };
    return descMap[name] || '';
  }
}
</script>
