<template>
  <div>
    <div class="pager-bar">
      <center>
        <ul class="pagination">
          <li v-for="page in pagerItems" v-bind:class="page.class">
            <a
              v-if="!!callback"
              v-bind:class="{ disabled: page.class !== 'active' }"
              v-on:click="callback(page.page)"
              >{{ page.label }}</a
            >
            <a
              v-else=""
              v-bind:href="page.url"
              v-bind:class="{ disabled: page.class !== 'active' }"
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
import { omegaup, T } from '../../omegaup';
import { types } from '../../api_types';

@Component
export default class Paginator extends Vue {
  @Prop() pagerItems!: types.PageItem[];
  @Prop({ required: false }) callback!: Function;

  T = T;
  page = 1;

  created() {
    if (!!this.callback) {
      this.callback(this.page);
    }
  }
}
</script>
