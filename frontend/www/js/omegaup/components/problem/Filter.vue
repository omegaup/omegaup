<template>
    <div class="card mb-3 panel panel-primary">

    </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import * as typeahead from '../../typeahead';

@Component({
  components: {
    'omegaup-autocomplete': Autocomplete,
  },
})

export default class CollectionFilter extends Vue {

}
</script>