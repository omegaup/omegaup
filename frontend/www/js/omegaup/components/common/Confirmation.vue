<template>
  <div class="mask">
    <div class="modal-dialog confirmation" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button
            class="close"
            type="button"
            data-dismiss="modal"
            @click.prevent="$emit('close')"
          >
            ×
          </button>
        </div>
        <div class="modal-body">
          <p>{{ question }}</p>
        </div>
        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-primary"
            data-dismiss="modal"
            @click.prevent="$emit('yes')"
          >
            {{ answerYes }}
          </button>
          <button
            type="button"
            class="btn btn-danger"
            data-dismiss="modal"
            @click.prevent="$emit('no')"
          >
            {{ answerNo }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';

@Component
export default class Paginator extends Vue {
  @Prop() question!: string;
  @Prop() answerYes!: string;
  @Prop() answerNo!: string;
}
</script>

<style lang="scss" scoped>
.mask {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(
    var(--quality-nomination-details-confirmation-background-color),
    0.5
  );
  transition: opacity 0.3s ease;
  display: flex;
  justify-content: center;
  align-items: center;
}

.confirmation {
  position: absolute;
}
</style>
