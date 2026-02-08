<template>
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="card-title mb-0">{{ T.gsocEditionsList }}</h4>
      <button class="btn btn-primary btn-sm" @click="showCreateForm = true">
        {{ T.gsocCreateEdition }}
      </button>
    </div>
    <div class="card-body">
      <div v-if="loading" class="text-center">
        <div class="spinner-border" role="status">
          <span class="sr-only">{{ T.wordsLoading }}</span>
        </div>
      </div>

      <div v-else-if="editions.length === 0" class="alert alert-info">
        {{ T.gsocNoEditionsFound }}
      </div>

      <div v-else class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>{{ T.gsocYear }}</th>
              <th>{{ T.gsocIsActive }}</th>
              <th>{{ T.gsocApplicationDeadline }}</th>
              <th>{{ T.wordsActions }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="edition in editions" :key="edition.edition_id">
              <td>{{ edition.year }}</td>
              <td>
                <span
                  class="badge"
                  :class="{
                    'badge-success': edition.is_active,
                    'badge-secondary': !edition.is_active,
                  }"
                >
                  {{ edition.is_active ? T.wordsYes : T.wordsNo }}
                </span>
              </td>
              <td>
                {{ edition.application_deadline || '-' }}
              </td>
              <td>
                <button
                  class="btn btn-sm btn-primary"
                  @click="editEdition(edition)"
                >
                  {{ T.wordsEdit }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import T from '../../lang';
import { GSoC } from '../../api';
import * as ui from '../../ui';

@Component
export default class EditionList extends Vue {
  T = T;
  editions: any[] = [];
  loading = false;
  showCreateForm = false;

  async mounted(): Promise<void> {
    await this.loadEditions();
  }

  async loadEditions(): Promise<void> {
    this.loading = true;
    try {
      const response = await GSoC.listEditions();
      this.editions = response.editions || [];
    } catch (error) {
      ui.error(String(error));
    } finally {
      this.loading = false;
    }
  }

  editEdition(edition: any): void {
    // TODO: Implement edition edit form
    alert('Edition editing not yet implemented');
  }
}
</script>

