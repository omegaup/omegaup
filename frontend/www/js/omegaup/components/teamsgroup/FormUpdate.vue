<template>
  <omegaup-teams-group-form
    :alias="alias"
    :description.sync="currentDescription"
    :name.sync="currentName"
    :number-of-contestants.sync="currentNumberOfContestants"
    :max-number-of-contestants="maxNumberOfContestants"
    @submit="(request) => $emit('update-teams-group', request)"
  >
    <template #teams-group-submit-button>
      {{ T.teamsGroupFormUpdate }}
    </template>
  </omegaup-teams-group-form>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import type { PropType } from 'vue';
import teamsgroup_FormBase from './FormBase.vue';
import T from '../../lang';

export default defineComponent({
  name: 'teamsGroupFormUpdate',
  components: {
    'omegaup-teams-group-form': teamsgroup_FormBase,
  },
  props: {
    alias: {
      type: String as PropType<null | string>,
      default: null,
    },
    description: {
      type: String as PropType<null | string>,
      default: null,
    },
    name: {
      type: String as PropType<null | string>,
      default: null,
    },
    numberOfContestants: {
      type: Number,
      default: 3,
    },
    maxNumberOfContestants: {
      type: Number,
      default: 10,
    },
  },
  emits: ['update-teams-group'],
  data() {
    return {
      T,
      currentDescription: this.description as null | string,
      currentName: this.name as null | string,
      currentNumberOfContestants: this.numberOfContestants,
    };
  },
});
</script>
