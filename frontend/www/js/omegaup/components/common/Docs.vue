<template>
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">{{ T.userDocsDocumentation }}</h2>
    </div>
    <ul v-for="(type, name) in docs" :key="name">
      <div class="h3" href="#">
        <font-awesome-icon
          :icon="getIcon(name)"
          :style="{ color: 'cornflowerblue' }"
        />
        {{ name }}
      </div>
      <li v-for="doc in type" :key="doc.name" class="list-unstyled">
        <a :href="doc.url">{{ doc.name }}</a>
      </li>
    </ul>
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
export default class CommonDocs extends Vue {
  @Prop() docs!: { [key: string]: types.UserDocument[] };

  T = T;
  ui = ui;

  getIcon(name: number | string): string[] {
    const icon = ['fas'];
    if (name === 'pdf') {
      icon.push('file-pdf');
    } else if (name === 'md') {
      icon.push('file');
    } else {
      icon.push('folder');
    }
    return icon;
  }
}
</script>
