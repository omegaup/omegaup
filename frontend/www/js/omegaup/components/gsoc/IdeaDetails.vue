<template>
  <div class="card">
    <!-- Info Bar -->
    <div class="card-header bg-light">
      <div class="row align-items-center">
        <div class="col-md-3">
          <small class="text-muted d-block">Year</small>
          <strong>{{ editionYear }}</strong>
        </div>
        <div class="col-md-3">
          <small class="text-muted d-block">Status</small>
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
        </div>
        <div class="col-md-3" v-if="idea.skill_level">
          <small class="text-muted d-block">Skill Level</small>
          <strong>{{ idea.skill_level }}</strong>
        </div>
        <div class="col-md-3" v-if="idea.estimated_hours">
          <small class="text-muted d-block">Estimated Hours</small>
          <strong>{{ idea.estimated_hours }}</strong>
        </div>
      </div>
    </div>
    <div class="card-header">
      <h4 class="card-title mb-0"><strong>Title: </strong>{{ idea.title }}</h4>
    </div>
    <div class="card-body">

      <!-- Project Description -->
      <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <strong>Description:</strong>
          <button
            v-if="isAdmin && !isEditingDescription"
            class="btn btn-sm btn-outline-primary"
            @click="startEditingDescription"
          >
            {{ T.wordsEdit }}
          </button>
          <div v-if="isAdmin && isEditingDescription">
            <button
              class="btn btn-sm btn-success mr-1"
              @click="saveDescription"
            >
              Save
            </button>
            <button
              class="btn btn-sm btn-secondary"
              @click="cancelEditingDescription"
            >
              {{ T.wordsCancel }}
            </button>
          </div>
        </div>
        <p v-if="!isEditingDescription" class="mb-0">
          {{ idea.brief_description || 'No description available' }}
        </p>
        <textarea
          v-else
          v-model="editedDescription"
          class="form-control"
          rows="5"
        ></textarea>
      </div>

      <div v-if="idea.expected_results" class="mb-3">
        <strong>Results:</strong>
        <p class="mb-0">{{ idea.expected_results }}</p>
      </div>

      <!-- Requirements & Skills -->
      <div v-if="idea.preferred_skills" class="mb-3">
        <strong>Skills: </strong>{{ idea.preferred_skills }}
      </div>


      <!-- Mentors & Contributors -->
      <div v-if="idea.possible_mentors" class="mb-3">
        <strong>Mentor:</strong> {{ idea.possible_mentors }}
      </div>

      <div v-if="idea.contributor_username" class="mb-3">
        <strong>Contributor:</strong> {{ idea.contributor_username }}
      </div>

      <!-- Links -->
      <div v-if="idea.blog_link" class="mb-3">
        <strong>Repository Link:</strong>
        <a :href="idea.blog_link" target="_blank" rel="noopener noreferrer">
          {{ idea.blog_link }}
        </a>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { GSoC } from '../../api';
import * as ui from '../../ui';

@Component
export default class IdeaDetails extends Vue {
  @Prop() idea!: any;
  @Prop() editionYear!: string;
  @Prop({ default: false }) isAdmin!: boolean;

  T = T;
  isEditingDescription = false;
  editedDescription = '';

  startEditingDescription(): void {
    this.editedDescription = this.idea.brief_description || '';
    this.isEditingDescription = true;
  }

  cancelEditingDescription(): void {
    this.isEditingDescription = false;
    this.editedDescription = '';
  }

  async saveDescription(): Promise<void> {
    try {
      await GSoC.updateIdea({
        idea_id: this.idea.idea_id,
        brief_description: this.editedDescription,
      });
      ui.success(T.gsocIdeaUpdated);
      this.idea.brief_description = this.editedDescription;
      this.isEditingDescription = false;
      this.$emit('updated');
    } catch (error) {
      ui.error(String(error));
    }
  }
}
</script>
