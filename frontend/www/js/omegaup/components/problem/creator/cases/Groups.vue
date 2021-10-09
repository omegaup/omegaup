<template>
  <div>
    <div class="w-100" v-for="group in groups" :key="group.groupId">
      <Group
        v-if="group.groupId === NIL ? group.cases.length !== 0 : true"
        :name="group.name"
        :points="group.points"
        :defined="group.defined"
        :groupId="group.groupId"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import { types } from '../../../../problem/creator/types';
import { namespace } from 'vuex-class';
import { NIL } from 'uuid';
import Group from './Group.vue';
const caseStore = namespace('casesStore');

@Component({
  components: { Group },
})
export default class Groups extends Vue {
  @caseStore.State('groups') groups!: types.Group[];
  NIL = NIL;
}
</script>

<style lang="scss"></style>
