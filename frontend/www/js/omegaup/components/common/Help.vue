<template>
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">{{ T.omegaupTitleHelp }}</h2>
    </div>
    <div class="card-body">
      <p class="lead">{{ T.helpWelcomeMessage }}</p>
      <div class="row mt-4">
        <div
          v-for="resource in helpConfig"
          :key="resource.url"
          class="col-md-6 col-lg-4 mb-4"
        >
          <div class="card h-100 shadow-sm">
            <div class="card-body">
              <h5 class="card-title">
                <span v-if="resource.icon" class="mr-2">
                  <font-awesome-icon
                    :icon="['fas', getIconName(resource.icon)]"
                  />
                </span>
                {{ localized(resource.title) }}
              </h5>
              <p class="card-text">
                {{ localized(resource.description) }}
              </p>
              <a
                :href="resource.url"
                :target="resource.external ? '_blank' : '_self'"
                :rel="resource.external ? 'noopener noreferrer' : ''"
                class="btn btn-primary btn-sm"
              >
                {{ T.helpVisit }}
                <font-awesome-icon
                  v-if="resource.external"
                  class="ml-2"
                  :icon="['fas', 'external-link-alt']"
                />
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
  faBook,
  faBookOpen,
  faCode,
  faComments,
  faExternalLinkAlt,
  faNewspaper,
  faVideo,
} from '@fortawesome/free-solid-svg-icons';
import T from '../../lang';
import helpConfig from '../../help.config';

library.add(
  faVideo,
  faComments,
  faNewspaper,
  faBook,
  faBookOpen,
  faCode,
  faExternalLinkAlt,
);

@Component({
  components: {
    FontAwesomeIcon,
  },
})
export default class CommonHelp extends Vue {
  T = T;
  helpConfig = helpConfig;

  localized(dict: { en: string; es: string; pt: string }): string {
    return dict[this.T.locale as 'en' | 'es' | 'pt'] || dict.en;
  }

  getIconName(iconName: string): string {
    const iconMap: { [key: string]: string } = {
      video: 'video',
      chat: 'comments',
      news: 'newspaper',
      book: 'book',
      docs: 'book-open',
      code: 'code',
    };
    return iconMap[iconName] || 'circle';
  }
}
</script>
