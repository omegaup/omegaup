<template>
  <div>
    <div class="pager-bar">
      <center>
        <ul class="pagination">
          <li v-for="page in pagerItems" :class="page.class">
            <a
              v-if="page.url"
              :href="page.url"
              :class="{ disabled: page.class !== 'active' }"
              >{{ page.label }}</a
            >
            <a
              v-else
              :class="{ disabled: page.class !== 'active' }"
              @click.prevent="$emit('page-changed', page.page)"
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
import T from '../../lang';
import { types } from '../../api_types';

@Component
export default class Paginator extends Vue {
  @Prop() pagerItems!: types.PageItem[];

  T = T;
}
</script>
