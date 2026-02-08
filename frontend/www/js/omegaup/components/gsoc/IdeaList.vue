<template>
  <div class="card">
    <div class="card-header">
      <h4 class="card-title">{{ T.gsocIdeasList }}</h4>
    </div>
    <div class="card-body">
      <div class="row mb-3">
        <div class="col-md-4">
          <label class="form-label">{{ T.gsocEdition }}</label>
          <select
            v-model="selectedEditionId"
            class="form-control"
            @change="onEditionChange"
          >
            <option :value="null">All year</option>
            <option
              v-for="edition in editions"
              :key="edition.edition_id"
              :value="edition.edition_id"
            >
              {{ edition.year }}
            </option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">{{ T.gsocStatus }}</label>
          <select
            v-model="selectedStatus"
            class="form-control"
            @change="onStatusChange"
            data-status-selector
          >
            <option :value="null">All status</option>
            <option
              v-for="status in statuses"
              :key="status.value"
              :value="status.value"
            >
              {{ status.label }}
            </option>
          </select>
        </div>
        <div class="col-md-4 text-right">
          <button
            v-if="isAdmin"
            class="btn btn-primary"
            @click="showCreateForm = true"
          >
            {{ T.gsocCreateIdea }}
          </button>
        </div>
      </div>

      <div v-if="loading" class="text-center">
        <div class="spinner-border" role="status">
          <span class="sr-only">{{ T.wordsLoading }}</span>
        </div>
      </div>

      <div v-else-if="ideas.length === 0" class="alert alert-info">
        {{ T.gsocNoIdeasFound }}
      </div>

      <template v-else>
        <!-- Table with Category Header -->
        <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
          <!-- Category Header Row -->
          <thead>
            <tr class="bg-light">
              <th class="text-center border-bottom-0">Title</th>
              <th class="text-center border-bottom-0">Year</th>
              <th class="text-center border-bottom-0">Status</th>
              <th class="text-center border-bottom-0">Level</th>
              <th class="text-center border-bottom-0">Hours</th>
              <th v-if="isAdmin" class="text-center border-bottom-0">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="idea in ideas" :key="idea.idea_id">
              <td>
                <a
                  href="#"
                  class="idea-title-link"
                  @click.prevent="showIdeaDetails(idea)"
                >
                  <strong>{{ idea.title }}</strong>
                </a>
                <br />
                <small class="text-muted">{{
                  idea.brief_description
                    ? idea.brief_description.substring(0, 100) + '...'
                    : ''
                }}</small>
              </td>
              <td>{{ getEditionYear(idea.edition_id) }}</td>
              <td>
                <span
                  class="badge"
                  :class="{
                    'badge-warning': idea.status === 'Proposed',
                    'badge-success': idea.status === 'Accepted',
                    'badge-secondary': idea.status === 'Archived',
                  }"
                >
                  {{ idea.status }}
                </span>
              </td>
              <td>{{ idea.skill_level || '-' }}</td>
              <td>{{ idea.estimated_hours || '-' }}</td>
              <td v-if="isAdmin" @click.stop>
                <button
                  class="btn btn-sm btn-primary"
                  @click="editIdea(idea)"
                >
                  {{ T.wordsEdit }}
                </button>
                <button
                  class="btn btn-sm btn-danger"
                  @click="deleteIdea(idea.idea_id)"
                >
                  {{ T.wordsDelete }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        </div>
      </template>
    </div>

    <!-- Modal for showing full idea details -->
    <div
      v-if="selectedIdea"
      class="modal fade show"
      style="display: block"
      tabindex="-1"
      role="dialog"
      @click.self="closeIdeaDetails"
    >
      <div class="modal-dialog modal-lg" role="document" @click.stop>
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ selectedIdea.title }}</h5>
            <button
              type="button"
              class="close"
              aria-label="Close"
              @click="closeIdeaDetails"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <idea-details
              :idea="selectedIdea"
              :edition-year="getEditionYear(selectedIdea.edition_id)"
              :is-admin="isAdmin"
              @updated="onIdeaUpdated"
            ></idea-details>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              @click="closeIdeaDetails"
            >
              {{ T.wordsClose }}
            </button>
          </div>
        </div>
      </div>
    </div>
    <div v-if="selectedIdea" class="modal-backdrop fade show"></div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { GSoC } from '../../api';
import * as ui from '../../ui';
import IdeaDetails from './IdeaDetails.vue';

@Component({
  components: {
    'idea-details': IdeaDetails,
  },
})
export default class IdeaList extends Vue {
  @Prop({ default: false }) isAdmin!: boolean;

  T = T;
  ideas: any[] = [];
  editions: any[] = [];
  loading = false;
  selectedEditionId: number | null = null;
  selectedStatus: string | null = null; // null = "All status"
  showCreateForm = false;
  selectedIdea: any = null;

  async mounted(): Promise<void> {
    await this.loadEditions();
    // Default to "All year" (selectedEditionId = null)
    this.selectedEditionId = null;
    // Default to "All status" (selectedStatus = null)
    this.selectedStatus = null;
    await this.loadIdeas();
  }

  async loadEditions(): Promise<void> {
    try {
      const response = await GSoC.listEditions();
      this.editions = response.editions || [];
    } catch (error) {
      ui.error(String(error));
    }
  }

  onEditionChange(): void {
    this.loadIdeas();
  }

  onStatusChange(): void {
    this.loadIdeas();
  }

  async loadIdeas(): Promise<void> {
    this.loading = true;
    try {
      const params: any = {};
      if (this.selectedEditionId !== null && this.selectedEditionId !== undefined) {
        params.edition_id = this.selectedEditionId;
      }
      if (this.selectedStatus !== null && this.selectedStatus !== undefined) {
        params.status = this.selectedStatus;
      }
      const response = await GSoC.listIdeas(params);
      this.ideas = response.ideas || [];
    } catch (error) {
      console.error('Error loading ideas:', error);
      ui.error(String(error));
    } finally {
      this.loading = false;
    }
  }

  get statuses(): Array<{ value: string; label: string }> {
    return [
      { value: 'Proposed', label: this.T.gsocStatusProposed },
      { value: 'Accepted', label: this.T.gsocStatusAccepted },
      { value: 'Archived', label: this.T.gsocStatusArchived },
    ];
  }

  getEditionYear(editionId: number): string {
    const edition = this.editions.find((e) => e.edition_id === editionId);
    return edition ? edition.year.toString() : '-';
  }

  showIdeaDetails(idea: any): void {
    this.selectedIdea = idea;
  }

  closeIdeaDetails(): void {
    this.selectedIdea = null;
  }

  async onIdeaUpdated(): Promise<void> {
    await this.loadIdeas();
    // Update the selected idea in the list
    if (this.selectedIdea) {
      const updatedIdea = this.ideas.find(
        (i) => i.idea_id === this.selectedIdea.idea_id
      );
      if (updatedIdea) {
        this.selectedIdea = updatedIdea;
      }
    }
  }

  editIdea(idea: any): void {
    this.$emit('edit-idea', idea);
  }

  async deleteIdea(ideaId: number): Promise<void> {
    if (!confirm(T.gsocConfirmDeleteIdea)) {
      return;
    }
    try {
      await GSoC.deleteIdea({ idea_id: ideaId });
      ui.success(T.gsocIdeaDeleted);
      await this.loadIdeas();
    } catch (error) {
      ui.error(String(error));
    }
  }
}
</script>

<style scoped>
.idea-title-link {
  color: #0066cc;
  text-decoration: none;
  cursor: pointer;
}

.idea-title-link:hover {
  color: #004499;
  text-decoration: underline;
}

/* Ensure select dropdown text is visible */
select.form-control {
  color: #333 !important;
  background-color: #fff !important;
}

select.form-control option {
  color: #333 !important;
  background-color: #fff !important;
}

/* Specifically target the status selector */
select.form-control[data-status-selector] {
  color: #333 !important;
  background-color: #fff !important;
}

select.form-control[data-status-selector] option {
  color: #333 !important;
  background-color: #fff !important;
}

/* Ensure selected option text is visible */
select.form-control[data-status-selector]:focus {
  color: #333 !important;
  background-color: #fff !important;
}

.table-responsive table {
  table-layout: auto;
  width: 100%;
}

.table-responsive th,
.table-responsive td {
  padding: 0.75rem;
  vertical-align: top;
}

.table-responsive th.text-center,
.table-responsive td {
  text-align: left;
}

.table-responsive th.text-center {
  text-align: center;
}

.table-responsive td:nth-child(2),
.table-responsive th:nth-child(2) {
  text-align: center;
  width: 8%;
}

.table-responsive td:nth-child(3),
.table-responsive th:nth-child(3) {
  text-align: center;
  width: 10%;
}

.table-responsive td:nth-child(4),
.table-responsive th:nth-child(4) {
  text-align: center;
  width: 12%;
}

.table-responsive td:nth-child(5),
.table-responsive th:nth-child(5) {
  text-align: center;
  width: 10%;
}

.table-responsive td:nth-child(6),
.table-responsive th:nth-child(6) {
  text-align: center;
  width: 15%;
}
</style>
