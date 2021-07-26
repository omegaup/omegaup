<template>
  <omegaup-clarification-list
    :clarifications="clarifications"
    :is-admin="isAdmin"
    :allow-filter-by-assignment="true"
    :length="length"
    :page="page"
    :pager-items="pagerItems"
    @clarification-response="
      (request) => $emit('clarification-response', request)
    "
  >
    <template #table-title>
      <th class="text-center" scope="col">{{ T.wordsHomework }}</th>
      <th class="text-center" scope="col">{{ T.wordsProblem }}</th>
    </template>
  </omegaup-clarification-list>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';

import clarification_List from '../arena/ClarificationList.vue';

@Component({
  components: {
    'omegaup-clarification-list': clarification_List,
  },
})
export default class CourseClarifications extends Vue {
  @Prop() isAdmin!: boolean;
  @Prop() clarifications!: types.Clarification[];
  @Prop() pagerItems!: types.PageItem[];
  @Prop() length!: number;
  @Prop() page!: number;

  T = T;
}
</script>
