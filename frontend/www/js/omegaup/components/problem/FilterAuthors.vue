<template>
  <div class="card">
    <div class="card-body">
      <h3>{{ T.problemCollectionAuthors }}</h3>
      <div
        v-for="author in authors.ranking"
        :key="author.username"
        class="form-check"
      >
        <label class="form-check-label">
          <input
            v-model="selectedAuthors"
            :value="author.username"
            class="form-check-input"
            type="checkbox"
          />
          <omegaup-user-username
            :linkify="true"
            :username="author.username"
            :name="author.name"
            :classname="author.classname"
          ></omegaup-user-username>
        </label>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';
import VueTypeaheadBootstrap from 'vue-typeahead-bootstrap';
import user_Username from '../user/Username.vue';

@Component({
  components: {
    'vue-typeahead-bootstrap': VueTypeaheadBootstrap,
    'omegaup-user-username': user_Username,
  },
})
export default class FilterAuthors extends Vue {
  @Prop() authors!: types.AuthorsRank;

  T = T;
  selectedAuthors: string[] = [];
}
</script>
