<template>
  <div class="card">
    <div class="card-header">
      <h4 class="card-title">
        {{ isEditing ? T.gsocEditIdea : T.gsocCreateIdea }}
      </h4>
    </div>
    <div class="card-body">
      <form @submit.prevent="onSubmit">
        <div class="form-group">
          <label>{{ T.gsocEdition }} *</label>
          <select
            v-model="formData.edition_id"
            class="form-control"
            required
            :disabled="isEditing"
          >
            <option :value="null">{{ T.gsocSelectEdition }}</option>
            <option
              v-for="edition in editions"
              :key="edition.edition_id"
              :value="edition.edition_id"
            >
              {{ edition.year }}
            </option>
          </select>
        </div>

        <div class="form-group">
          <label>{{ T.gsocTitle }} *</label>
          <input
            v-model="formData.title"
            type="text"
            class="form-control"
            required
          />
        </div>

        <div class="form-group">
          <label>{{ T.gsocBriefDescription }}</label>
          <textarea
            v-model="formData.brief_description"
            class="form-control"
            rows="3"
          ></textarea>
        </div>

        <div class="form-group">
          <label>{{ T.gsocExpectedResults }}</label>
          <textarea
            v-model="formData.expected_results"
            class="form-control"
            rows="3"
          ></textarea>
        </div>

        <div class="form-group">
          <label>{{ T.gsocPreferredSkills }}</label>
          <textarea
            v-model="formData.preferred_skills"
            class="form-control"
            rows="2"
          ></textarea>
        </div>

        <div class="form-group">
          <label>{{ T.gsocPossibleMentors }}</label>
          <textarea
            v-model="formData.possible_mentors"
            class="form-control"
            rows="2"
          ></textarea>
        </div>

        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label>{{ T.gsocEstimatedHours }}</label>
              <input
                v-model.number="formData.estimated_hours"
                type="number"
                class="form-control"
                min="0"
              />
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label>{{ T.gsocSkillLevel }}</label>
              <select v-model="formData.skill_level" class="form-control">
                <option :value="null">{{ T.gsocSelectSkillLevel }}</option>
                <option value="Low">{{ T.gsocSkillLevelLow }}</option>
                <option value="Medium">{{ T.gsocSkillLevelMedium }}</option>
                <option value="Advanced">{{ T.gsocSkillLevelAdvanced }}</option>
              </select>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label>{{ T.gsocStatus }}</label>
              <select v-model="formData.status" class="form-control">
                <option value="Proposed">{{ T.gsocStatusProposed }}</option>
                <option value="Accepted">{{ T.gsocStatusAccepted }}</option>
                <option value="Archived">{{ T.gsocStatusArchived }}</option>
              </select>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label>{{ T.gsocBlogLink }}</label>
          <input
            v-model="formData.blog_link"
            type="url"
            class="form-control"
            placeholder="https://..."
          />
        </div>

        <div class="form-group">
          <label>{{ T.gsocContributorUsername }}</label>
          <input
            v-model="formData.contributor_username"
            type="text"
            class="form-control"
          />
        </div>

        <div class="form-group text-right">
          <button type="button" class="btn btn-secondary" @click="onCancel">
            {{ T.wordsCancel }}
          </button>
          <button type="submit" class="btn btn-primary" :disabled="saving">
            {{ saving ? T.wordsSaving : T.wordsSave }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import T from '../../lang';
import { GSoC } from '../../api';
import * as ui from '../../ui';

@Component
export default class IdeaForm extends Vue {
  @Prop({ default: null }) idea!: any;
  @Prop({ default: [] }) editions!: any[];

  T = T;
  saving = false;
  formData: any = {
    edition_id: null,
    title: '',
    brief_description: '',
    expected_results: '',
    preferred_skills: '',
    possible_mentors: '',
    estimated_hours: null,
    skill_level: null,
    status: 'Proposed',
    blog_link: '',
    contributor_username: '',
  };

  get isEditing(): boolean {
    return this.idea !== null;
  }

  @Watch('idea', { immediate: true })
  onIdeaChange(): void {
    if (this.idea) {
      this.formData = { ...this.idea };
    } else {
      this.resetForm();
    }
  }

  resetForm(): void {
    this.formData = {
      edition_id: null,
      title: '',
      brief_description: '',
      expected_results: '',
      preferred_skills: '',
      possible_mentors: '',
      estimated_hours: null,
      skill_level: null,
      status: 'Proposed',
      blog_link: '',
      contributor_username: '',
    };
  }

  async onSubmit(): Promise<void> {
    this.saving = true;
    try {
      const params: any = { ...this.formData };
      // Remove null/empty values
      Object.keys(params).forEach((key) => {
        if (params[key] === null || params[key] === '') {
          delete params[key];
        }
      });

      if (this.isEditing) {
        params.idea_id = this.idea.idea_id;
        await GSoC.updateIdea(params);
        ui.success(T.gsocIdeaUpdated);
      } else {
        await GSoC.createIdea(params);
        ui.success(T.gsocIdeaCreated);
      }
      this.$emit('saved');
      this.resetForm();
    } catch (error) {
      ui.error(String(error));
    } finally {
      this.saving = false;
    }
  }

  onCancel(): void {
    this.$emit('cancel');
    this.resetForm();
  }
}
</script>
