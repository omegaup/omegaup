<template>
  <div>
    <div class="pager-bar">
      <center>
        <ul class="pagination">
          <li v-for="page in pagerItems" v-bind:class="page.class">
            <a
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
import { T } from '../../omegaup.js';
import omegaup from '../../api.js';

@Component
export default class Paginator extends Vue {
  @Prop() pagerItems!: omegaup.Paginator[];

  T = T;
}
</script>
