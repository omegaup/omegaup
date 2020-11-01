<template>
  <div class="card">
    <div class="card-body">
      <h3>{{ T.problemCollectionAuthors }}</h3>
      <div v-for="(author, index) in authors" :key="index" class="form-check">
        <label class="form-check-label">
          <input
            v-model="selectedAuthors"
            :value="author"
            class="form-check-input"
            type="checkbox"
          />
          <omegaup-user-username
            :linkify="true"
            :username="author"
          ></omegaup-user-username>
        </label>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import VueTypeaheadBootstrap from 'vue-typeahead-bootstrap';
import user_Username from '../user/Username.vue';

@Component({
  components: {
    'vue-typeahead-bootstrap': VueTypeaheadBootstrap,
    'omegaup-user-username': user_Username,
  },
})
export default class FilterAuthors extends Vue {
  @Prop() authors!: string[];

  T = T;
  selectedAuthors: string[] = [];
}
</script>
