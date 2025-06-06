<template>
  <div class="card solution-form">
    <div class="card-header">
      <h3 class="card-title mb-0">
        Write a Solution
      </h3>
    </div>
    <div class="text-center">
      <p class="mt-3 mb-0">
        {{ T.postSolutionFormFirstTime }}
        <strong>
          <a :href="howToWriteSolutionLink" target="_blank">
            {{ T.postSolutionFormHereIsHow }}
          </a>
        </strong>
      </p>
    </div>
    <div class="card-body px-2 px-sm-4">
      <form ref="form" method="POST" class="form" enctype="multipart/form-data">
        <div class="accordion mb-3">
          <div class="card">
            <div class="card-header">
              <h2 class="mb-0">
                <button
                  ref="solution-approach"
                  class="btn btn-link btn-block text-left"
                  type="button"
                  data-toggle="collapse"
                  data-target=".solution-approach"
                  aria-expanded="true"
                  aria-controls="solution-form-approach"
                >
                   Approach 
                </button>
              </h2>
            </div>
            <div class="collapse show card-body px-2 px-sm-4 solution-approach">
              <div class="row">
                <div class="form-group col-md-12">
                  <label class="control-label"></label>
                  <textarea
                    v-model="approach"
                    required
                    name="approach"
                    rows="4"
                    class="form-control"
                    :class="{ 'is-invalid': errors.includes('approach') }"
                  ></textarea>
                </div>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h2 class="mb-0">
                <button
                  ref="solution-complexity"
                  class="btn btn-link btn-block text-left"
                  type="button"
                  data-toggle="collapse"
                  data-target=".solution-complexity"
                  aria-expanded="true"
                  aria-controls="solution-form-complexity"
                >
                 Complexity Analysis
                </button>
              </h2>
            </div>
            <div class="collapse show card-body px-2 px-sm-4 solution-complexity">
              <div class="row">
                <div class="form-group col-md-6">
                  <label class="control-label">Time Complexity</label>
                  <input
                    v-model="timeComplexity"
                    required
                    name="time_complexity"
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': errors.includes('time_complexity') }"
                    placeholder="O(n), O(n log n), etc."
                  />
                </div>
                <div class="form-group col-md-6">
                  <label class="control-label">Space Complexity</label>
                  <input
                    v-model="spaceComplexity"
                    required
                    name="space_complexity"
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': errors.includes('space_complexity') }"
                    placeholder="O(n), O(1), etc."
                  />
                </div>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h2 class="mb-0">
                <button
                  ref="solution-code"
                  class="btn btn-link btn-block text-left"
                  type="button"
                  data-toggle="collapse"
                  data-target=".solution-code"
                  aria-expanded="true"
                  aria-controls="solution-form-code"
                >
                  Solution Code
                </button>
              </h2>
            </div>
            <div class="collapse show card-body px-2 px-sm-4 solution-code">
              <div class="row">
                <div class="form-group col-md-12">
                  <label class="control-label">Language</label>
                  <select
                    v-model="language"
                    name="language"
                    class="form-control"
                    :class="{ 'is-invalid': errors.includes('language') }"
                    required
                  >
                    <option
                      v-for="(languageText, languageName) in validLanguages"
                      :key="languageName"
                      :value="languageName"
                    >
                      {{ languageText }}
                    </option>
                  </select>
                </div>
              </div>
              <div class="row">
                <div class="form-group col-md-12">
                  <label class="control-label">{{ T.wordsCode }}</label>
                  <textarea
                    v-model="code"
                    required
                    name="code"
                    rows="10"
                    class="form-control code-editor"
                    :class="{ 'is-invalid': errors.includes('code') }"
                  ></textarea>
                </div>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h2 class="mb-0">
                <button
                  ref="solution-explanation"
                  class="btn btn-link btn-block text-left"
                  type="button"
                  data-toggle="collapse"
                  data-target=".solution-explanation"
                  aria-expanded="true"
                  aria-controls="solution-form-explanation"
                >
                Explanation
                </button>
              </h2>
            </div>
            <div class="collapse show card-body px-2 px-sm-4 solution-explanation">
              <div class="row">
                <div class="form-group col-md-12">
                  <label class="control-label">Step-by-step Explanation</label>
                  <textarea
                    v-model="explanation"
                    required
                    name="explanation"
                    rows="6"
                    class="form-control"
                    :class="{ 'is-invalid': errors.includes('explanation') }"
                  ></textarea>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="row">
          <div class="form-group col-md-6 no-bottom-margin">
            <button
              type="submit"
              class="btn btn-primary"
              @click="submitSolution"
            >
              Submit Solution
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Ref } from 'vue-property-decorator';
import T from '../../lang';

@Component
export default class PostSolution extends Vue {
  @Prop({ default: () => [] }) errors!: string[];
  @Prop({ default: () => ({}) }) data!: any;

  @Ref('form') formRef!: HTMLFormElement;

  T = T;
  approach = '';
  timeComplexity = '';
  spaceComplexity = '';
  code = '';
  explanation = '';
  language = '';
  
  // Sample languages - this would come from your API or props in a real implementation
  validLanguages = {
    'cpp': 'C++',
    'java': 'Java',
    'python': 'Python',
    'javascript': 'JavaScript',
    'golang': 'Go',
    'ruby': 'Ruby'
  };

  get howToWriteSolutionLink(): string {
    return 'https://example.com/how-to-write-solutions';
  }

  submitSolution(event: Event): void {
    // You can add validation logic here
    // Example:
    // if (!this.approach.trim()) {
    //   this.errors.push('approach');
    //   event.preventDefault();
    // }
    
    // Actual form submission would be handled by the form's action or 
    // you could handle it programmatically with fetch/axios
    console.log('Submitting solution', {
      approach: this.approach,
      timeComplexity: this.timeComplexity,
      spaceComplexity: this.spaceComplexity,
      code: this.code,
      explanation: this.explanation,
      language: this.language
    });
  }
}
</script>

<style>
.solution-form .code-editor {
  font-family: monospace;
  white-space: pre;
}

.solution-form textarea {
  resize: vertical;
}

.solution-form .card-header button {
  text-decoration: none;
  font-weight: 500;
  color: #212529;
}

.solution-form .card-header button:hover {
  color: #0056b3;
}

.solution-form .no-bottom-margin {
  margin-bottom: 0;
}
</style>