<template>
  <div class="d-flex justify-content-center">
    <ul class="pagination m-0">
      <li
        v-bind:key="index"
        v-for="(page, index) in pagerItems"
        class="page-item"
        v-bind:class="page.class"
      >
        <a
          class="page-link"
          v-if="page.url"
          v-bind:href="page.url"
          v-bind:class="{ disabled: page.class !== 'active' }"
          >{{ page.label }}</a
        >
        <a
          v-else
          class="page-link"
          v-bind:class="{ disabled: page.class !== 'active' }"
          v-on:click.prevent="$emit('page-changed', page.page)"
          >{{ page.label }}</a
        >
      </li>
    </ul>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import { types } from '../../api_types';

@Component
export default class Paginator extends Vue {
  @Prop() pagerItems!: types.PageItem[];

  T = T;
}
</script>
