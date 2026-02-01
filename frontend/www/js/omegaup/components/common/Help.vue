<template>
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">{{ T.omegaupTitleHelp }}</h2>
    </div>
    <div class="card-body">
      <p class="lead">{{ T.helpPageDescription }}</p>
      <div class="row mt-4">
        <div
          v-for="resource in helpResources"
          :key="resource.name"
          class="col-md-6 col-lg-4 mb-4"
        >
          <div class="card h-100 shadow-sm">
            <div class="card-body">
              <h5 class="card-title">
                <font-awesome-icon
                  :icon="getIcon(resource.icon)"
                  :style="{ color: '#007bff', marginRight: '10px' }"
                />
                {{ T[`helpResource${capitalizeFirst(resource.name)}`] }}
              </h5>
              <p class="card-text">
                {{
                  T[
                    `helpResource${capitalizeFirst(resource.name)}Description`
                  ]
                }}
              </p>
              <a
                :href="resource.url"
                :target="resource.external ? '_blank' : '_self'"
                :rel="resource.external ? 'noopener noreferrer' : ''"
                class="btn btn-primary btn-sm"
              >
                {{ T.helpVisitResource }}
                <font-awesome-icon
                  v-if="resource.external"
                  icon="external-link-alt"
                  class="ml-1"
                />
              </a>
            </div>
          </div>
        </div>
      </div>
      <hr class="my-4" />
      <div class="mt-4">
        <h4>{{ T.helpPageAdditionalResources }}</h4>
        <ul class="list-group list-group-flush">
          <li class="list-group-item">
            <a href="/rank/">{{ T.navUserRanking }}</a>
          </li>
          <li class="list-group-item">
            <a
              href="https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/How-to-write-problems-for-omegaUp.md"
              target="_blank"
              rel="noopener noreferrer"
              >{{ T.helpHowToWriteProblems }}</a
            >
          </li>
          <li class="list-group-item">
            <a href="/privacypolicy/">{{ T.privacyPolicy }}</a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import * as ui from '../../ui';
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
  ui = ui;

  getIcon(iconName: string): string[] {
    const iconMap: { [key: string]: string } = {
      video: 'video',
      comments: 'comments',
      blog: 'blog',
      book: 'book',
      'file-text': 'file-alt',
      github: 'code-branch',
    };
    return ['fas', iconMap[iconName] || 'question-circle'];
  }

  capitalizeFirst(str: string): string {
    return str.charAt(0).toUpperCase() + str.slice(1);
  }
}
</script>
