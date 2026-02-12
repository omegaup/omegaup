<template>
  <div class="container">
    <div class="row">
      <div class="col-12">
        <div class="h2">{{ T.gsocAdminPanel }}</div>
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-12">
        <ul class="nav nav-tabs">
          <li class="nav-item">
            <a
              class="nav-link"
              :class="{ active: activeTab === 'ideas' }"
              @click="activeTab = 'ideas'"
            >
              {{ T.gsocIdeas }}
            </a>
          </li>
          <li class="nav-item">
            <a
              class="nav-link"
              :class="{ active: activeTab === 'editions' }"
              @click="activeTab = 'editions'"
            >
              {{ T.gsocEditions }}
            </a>
          </li>
        </ul>
      </div>
    </div>

    <div v-if="activeTab === 'ideas'" class="row">
      <div class="col-12">
        <idea-list
          :is-admin="true"
          @edit-idea="onEditIdea"
        ></idea-list>
      </div>
    </div>

    <div v-if="activeTab === 'editions'" class="row">
      <div class="col-12">
        <edition-list></edition-list>
      </div>
    </div>

    <idea-form
      v-if="showIdeaForm"
      :idea="selectedIdea"
      :editions="editions"
      @saved="onIdeaSaved"
      @cancel="showIdeaForm = false"
    ></idea-form>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import T from '../../lang';
import { GSoC } from '../../api';
import IdeaList from './IdeaList.vue';
import IdeaForm from './IdeaForm.vue';
import EditionList from './EditionList.vue';

@Component({
  components: {
    'idea-list': IdeaList,
    'idea-form': IdeaForm,
    'edition-list': EditionList,
  },
})
export default class AdminPanel extends Vue {
  T = T;
  activeTab = 'ideas';
  showIdeaForm = false;
  selectedIdea: any = null;
  editions: any[] = [];

  async mounted(): Promise<void> {
    await this.loadEditions();
  }

  async loadEditions(): Promise<void> {
    try {
      const response = await GSoC.listEditions();
      this.editions = response.editions || [];
    } catch (error) {
      // Silently fail - editions will be loaded by child components
    }
  }

  onEditIdea(idea: any): void {
    this.selectedIdea = idea;
    this.showIdeaForm = true;
  }

  onIdeaSaved(): void {
    this.showIdeaForm = false;
    this.selectedIdea = null;
    // Refresh the idea list
    this.$nextTick(() => {
      const ideaList = this.$children.find(
        (child) => child.$options.name === 'IdeaList'
      );
      if (ideaList && typeof (ideaList as any).loadIdeas === 'function') {
        (ideaList as any).loadIdeas();
      }
    });
  }
}
</script>
