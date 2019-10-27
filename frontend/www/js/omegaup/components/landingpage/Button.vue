<template>
  <div>
    {{ this.label }}
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';

@Component
export default class Button extends Vue {
  @Prop() private label!: string;
  @Prop({ default: 'primary' }) private btnStyle!: string;

  get buttonClassnames() {
    // console.log({ primary: this.btnStyle === 'primary' })
    return {
      btn: true,
      primary: this.btnStyle === 'primary',
    };
  }
}

</script>

<style lang="scss" scoped>
$omegaup-primary-accent: #0275d8;
$omegaup-shadow: 0 3px 6px rgba(0, 0, 0, 0.16), 0 3px 6px rgba(0, 0, 0, 0.23);

.btn {
  // width: 120px;
  padding: 8px 24px;
  border-radius: 20px;
  font-weight: 500;

  &.primary {
    background-color: $omegaup-primary-accent;
    color: white;
  }

  &:hover {
    box-shadow: $omegaup-shadow;
  }
}
</style>
