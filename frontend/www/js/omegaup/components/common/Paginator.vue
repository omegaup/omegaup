<template>
  <div>
    <div class="pager-bar">
      <center>
        <ul class="pagination">
          <li v-for="page in pagerItems" v-bind:class="page.class">
            <a
              v-if="page.url"
              v-bind:href="page.url"
              v-bind:class="{ disabled: page.class !== 'active' }"
              >{{ page.label }}</a
            >
            <a
              v-else
              v-bind:class="{ disabled: page.class !== 'active' }"
              v-on:click.prevent="$emit('page-changed', page.page)"
              >{{ page.label }}</a
            >
          </li>
        </ul>
      </center>
    </div>
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
