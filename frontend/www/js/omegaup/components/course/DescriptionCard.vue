<template>
  <div class="card-header mb-3">
    <h1>{{ T.courseCardAboutCourses }}</h1>
    <p>{{ T.courseCardDescriptionCourses }}</p>
    <div class="text-right align-middle">
      <a v-bind:href="`https://blog.omegaup.com/cursos-en-omegaup/`">{{
        T.wordsReadMore
      }}</a>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import * as ui from '../../ui';
import T from '../../lang';

@Component
export default class DescriptionCard extends Vue {
  T = T;
}
</script>
