<template>
  <table class="table table-striped table-hover table-responsive-sm">
    <thead>
      <tr>
        <th scope="col" class="text-center"></th>
        <th scope="col" class="text-center">
          {{ T.codersOfTheMonthUser }}
        </th>
        <th scope="col" class="text-center">
          {{ T.codersOfTheMonthCountry }}
        </th>
        <th scope="col" class="text-center">
          {{ T.codersOfTheMonthDate }}
        </th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="(coder, index) in coders" :key="index">
        <td class="text-center">
          <img :src="coder.gravatar_32" />
        </td>
        <td class="text-center align-middle">
          <omegaup-user-username
            :classname="coder.classname"
            :linkify="true"
            :username="coder.username"
          ></omegaup-user-username>
        </td>
        <td class="text-center align-middle">
          <omegaup-countryflag
            :country="coder.country_id"
          ></omegaup-countryflag>
        </td>
        <td class="text-center align-middle">
          {{ coder.date }}
        </td>
      </tr>
    </tbody>
  </table>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import user_Username from '../user/Username.vue';
import country_Flag from '../CountryFlag.vue';
import { types } from '../../api_types';

@Component({
  components: {
    'omegaup-user-username': user_Username,
    'omegaup-countryflag': country_Flag,
  },
})
export default class CoderOfTheMonthList extends Vue {
  @Prop() coders!: types.CoderOfTheMonthList[];

  T = T;
}
</script>
