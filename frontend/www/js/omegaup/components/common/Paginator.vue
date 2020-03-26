<template>
  <div>
    <div class="pager-bar">
      <center>
        <ul class="pagination">
          <li v-for="page in pagerItems" v-bind:class="page.class">
            <a
              v-if="!requireCallback"
              v-bind:href="page.url"
              v-bind:class="{ disabled: page.class !== 'active' }"
              >{{ page.label }}</a
            ><a
              v-else=""
              v-bind:class="{ disabled: page.class !== 'active' }"
              v-on:click="callback(page.page)"
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

@Component
export default class Paginator extends Vue {
  @Prop() pagerItems!: omegaup.Paginator[];
  @Prop() requireCallback!: boolean;
  @Prop() callback!: Function;

  T = T;
  page = 1;

  mounted() {
    if (this.requireCallback) {
      this.callback(this.page);
    }
  }
}
</script>
