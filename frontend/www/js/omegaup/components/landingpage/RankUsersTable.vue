<template>
  <div class="ranks-table">
    <h3 class="rank-title">Usuarios con mayor puntaje</h3>

    <UICard>
      <table class="users-table">
        <tr>
          <th>#</th>
          <th>Usuario</th>
          <th>Problemas Resueltos</th>
          <th>Puntuación</th>
        </tr>

        <tr v-for="n in 5" :key="n">
          <td>{{ n }}</td>
          <td class="item-name">Usuario {{ n }}</td>
          <td>15</td>
          <td>23</td>
        </tr>
      </table>

      <UIButton label="Ver ranking completo" />
    </UICard>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import OmegaupButton from './Button.vue';
import OmegaupCard from './Card.vue';

@Component({
  components: {
    UIButton: OmegaupButton,
    UICard: OmegaupCard,
  },
})
export default class RankSchoolsTable extends Vue {
}
</script>

<style lang="scss" scoped>
</style>