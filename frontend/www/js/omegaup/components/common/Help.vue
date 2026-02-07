<template>
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">{{ T.omegaupTitleHelp }}</h2>
    </div>
    <div class="card-body">
      <p>{{ T.helpDescription }}</p>
      <ul class="list-unstyled">
        <li v-for="resource in helpResources" :key="resource.name" class="mb-3">
          <a
            :href="resource.url"
            :target="resource.external ? '_blank' : '_self'"
            :rel="resource.external ? 'noopener noreferrer' : ''"
          >
            <font-awesome-icon
              :icon="getIcon(resource.name)"
              class="mr-2"
            />
            {{ getResourceLabel(resource.name) }}
            <font-awesome-icon
              v-if="resource.external"
              :icon="['fas', 'external-link-alt']"
              class="ml-1"
              size="xs"
            />
          </a>
        </li>
      </ul>
    </div>
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

  getIcon(name: string): string[] {
    const iconMap: { [key: string]: string } = {
      tutorials: 'video',
      discord: 'comments',
      omegaUpBlog: 'blog',
      algorithmsBook: 'book',
      documentation: 'file-alt',
      github: 'code-branch',
    };
    return ['fas', iconMap[name] || 'link'];
  }

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
}
</script>
