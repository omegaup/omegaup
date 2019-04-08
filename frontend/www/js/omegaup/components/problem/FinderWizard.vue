<template>
  <transition name="modal">
    <div class="modal-mask">
      <div class="modal-container">
        <button class="close"
             v-on:click="$emit('close')">❌</button> <form-wizard color="#678DD7"
             v-bind:back-button-text="T.wordBack"
             v-bind:finish-button-text="T.wordConfirm"
             v-bind:next-button-text="T.wordNext"
             v-bind:subtitle="T.wizardDescription"
             v-bind:title="T.wizardTitle"
             v-on:on-complete="searchProblems"><tab-content v-bind:title=
             "T.wizardStepOne"><toggle-button v-bind:color=
             "{checked: '#678DD7', unchecked: '#343a40'}"
                       v-bind:font-size="12"
                       v-bind:height="35"
                       v-bind:labels=
                       "{checked: `${T.wordKarel}`, unchecked: `${T.wordsAnyLanguage}`}"
                       v-bind:value="karel"
                       v-bind:width="160"
                       v-model="karel"></toggle-button> <tags-input element-id="tags"
                    v-bind:existing-tags="possibleTags"
                    v-bind:only-existing-tags="true"
                    v-bind:placeholder="T.wordsAddTag"
                    v-bind:typeahead="true"
                    v-model="selectedTags"></tags-input></tab-content> <tab-content v-bind:title=
                    "T.wizardStepTwo"><vue-slider tooltip="none"
                    v-bind:adsorb="true"
                    v-bind:dot-size="18"
                    v-bind:enable-cross="false"
                    v-bind:included="true"
                    v-bind:marks="sliderMarks"
                    v-bind:max="4"
                    v-bind:min="0"
                    v-model="difficultyRange"></vue-slider></tab-content> <tab-content v-bind:title=
                    "T.wizardStepThree">
          <div class="tab-select">
            <div class="tab-select-el"
                 v-bind:class="{ 'tab-select-el-active': priority.type === selectedPriority }"
                 v-bind:data-id="priority.type"
                 v-for="priority in priorities"
                 v-on:click="setPriority">
              {{ priority.text }}
            </div>
          </div>
        </tab-content></form-wizard>
      </div>
    </div>
  </transition>
</template>

<style>
.modal-mask {
  position: fixed;
  z-index: 99999;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, .5);
  transition: opacity .3s ease;
}

.modal-container {
  background: #eee;
  width: 800px;
  margin: 2.5em auto 0;
  border: 2px solid #ccc;
  padding: 1em;
  position: relative;
  overflow: auto;
}

.close {
  float: none;
  text-align: right;
  width: 100%;
}

.wizard-tab-content {
  text-align: center;
}

.tags-input {
  margin: 1em 0 3em;
}

.tags-input input {
    padding-left: .25em;
}

.tags-input-remove {
  width: 1rem;
  height: 1rem;
}

.tags-input-badge {
  font-size: 1em;
}

.typeahead-badges {
  margin-top: .35em;
}

.tags-input-remove:before, .tags-input-remove:after,
.tags-input-typeahead-item-highlighted-default,
.vue-slider-process {
  background-color: #678DD7;
}

.vue-slider {
  margin: 1em 2em 5em;
}

.vue-slider-rail {
  height: 12.5px;
}

.tab-select {
  margin: 2em 3em 3em;
  width: 90%;
  display: flex;
}

.tab-select-el {
  cursor: pointer;
  padding: .25em 1em;
  border: 1px solid #678DD7;
  flex: 1;
  text-align: center;
  color: #678DD7;
}

.tab-select-el:hover,
.tab-select-el-active {
  color: #FFF;
  background: #678DD7;
}
</style>

<script>
// https://binarcode.github.io/vue-form-wizard/
import {FormWizard, TabContent} from 'vue-form-wizard';
import 'vue-form-wizard/dist/vue-form-wizard.min.css';
// https://www.npmjs.com/package/vue-js-toggle-button
import {ToggleButton} from 'vue-js-toggle-button';
// https://github.com/voerro/vue-tagsinput
import VoerroTagsInput from '@voerro/vue-tagsinput';
import '@voerro/vue-tagsinput/dist/style.css';
// https://nightcatsama.github.io/vue-slider-component/
import VueSlider from 'vue-slider-component';
import 'vue-slider-component/theme/default.css';

import {OmegaUp, T, API} from '../../omegaup.js';

export default {
  data: function() {
    return {
      T, karel: true, possibleTags: {}, selectedTags:[], difficultyRange:[0, 4],
          sliderMarks:
              {
                '0': 'Muy fácil',
                '1': 'Fácil',
                '2': 'Normal',
                '3': 'Difícil',
                '4': 'Muy Difícil',
              },
          selectedPriority: 'quality', priorities:[
            {
              type: 'quality',
              text: T.wordsQuality,
            },
            {
              type: 'points',
              text: T.wordsPointsForRank,
            },
            {
              type: 'submissions',
              text: T.wizardPriorityPopularity,
            },
          ],
    }
  },
  mounted: function() {
    const self = this;
    omegaup.API.Tag.list({query: ''})
        .then(function(data) {
          data.forEach(tagObject => {self.possibleTags[tagObject.name] =
                                         tagObject.name});
        })
        .fail(omegaup.UI.apiError);
  },
  methods: {
    setPriority: function(e) {
      const self = this;
      self.selectedPriority = e.target.dataset.id;
    },
    searchProblems: function() {
      const self = this;
      // Build URL
      let url =
          `https://omegaup.com/problem/?some_tags=true${self.karel ? '&only_karel=true' : ''}`;
      if (self.selectedTags !== undefined && self.selectedTags.length > 0) {
        url += self.selectedTags.map((tag) => `&tag[]=${tag}`)
                   .reduce((query, tag) => query += tag);
      }
      url +=
          `&min_difficulty=${self.difficultyRange[0]}&max_difficulty=${self.difficultyRange[1]}`;
      url += `&order_by=${self.selectedPriority}&mode=desc`;
      window.location.href = url;
    },
  },
  components: {
    FormWizard,
    TabContent,
    ToggleButton,
    "tags-input": VoerroTagsInput,
    VueSlider,
  },
}

</script>
