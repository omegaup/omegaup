<template>
  <input class="form-control"
        size="16"
        type="text">
</template>

<script>
import {T} from '../omegaup.js';
export default {
  props: {
    value: Date,
    format: {
      type: String,
      'default': T.datePickerFormat,
    },
  },
  data: function() { return {};},
  watch: {
    value: function(val) { $(this.$el)
                               .datepicker('setValue', val);},
  },
  mounted: function() {
    var self = this;
    $(self.$el)
        .datepicker({
          weekStart: 1,
          format: self.format,
        })
        .on('changeDate', function(ev) { self.$emit('input', ev.date); })
        .datepicker('setValue', self.value);
  },
};
</script>
