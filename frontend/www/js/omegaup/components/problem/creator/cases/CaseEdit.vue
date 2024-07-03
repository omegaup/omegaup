<template>
  <div>
    <div
      v-if="getSelectedCase && getSelectedGroup"
      class="d-flex justify-content-between"
    >
      <div>
        <h3 class="mb-0 d-md-inline mr-2">{{ getSelectedCase.name }}</h3>
        <h5 class="mb-0 d-none d-md-inline text-muted">
          {{ getSelectedGroup.name }}
        </h5>
      </div>
      <div>
        <b-button variant="light" class="mr-2">
          <div class="container">
            <div class="row">
              <BIconPencilFill
                variant="info"
                font-scale="1.10"
                class="mr-1 pt-1"
              />
              {{ T.problemCreatorEditCase }}
            </div>
          </div>
        </b-button>
        <b-button variant="light" class="mr-2">
          <div class="container">
            <div class="row">
              <BIconTrashFill
                variant="danger"
                font-scale="1.20"
                class="mr-1 pt-1"
              />
              {{ T.problemCreatorDeleteCase }}
            </div>
          </div>
        </b-button>
        <b-button class="h-100" variant="light">
          <BIconThreeDotsVertical />
        </b-button>
      </div>
    </div>
    <hr class="border-top my-2" />
  </div>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import T from '../../../../lang';
import { namespace } from 'vuex-class';
import { Case, Group } from '@/js/omegaup/problem/creator/types';

const casesStore = namespace('casesStore');

@Component
export default class CaseEdit extends Vue {
  T = T;

  @casesStore.State('groups') groups!: Group[];
  @casesStore.Getter('getSelectedCase') getSelectedCase!: Case;
  @casesStore.Getter('getSelectedGroup') getSelectedGroup!: Group;
}
</script>
