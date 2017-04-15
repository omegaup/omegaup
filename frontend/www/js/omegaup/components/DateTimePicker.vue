<template>
  <input class="form-control"
        size="16"
        type="text">
</template>

<script>
export default {
  props: {
    value: Date,
    format: {
      type: String,
      'default': 'mm/dd/yyyy hh:ii',
    },
  },
  data: function() { return {};},
  watch: {
    value: function(val) { $(this.$el)
                               .data('datetimepicker')
                               .setDate(val);},
  },
  mounted: function() {
    var self = this;
    $(self.$el)
        .datetimepicker({
          format: self.format,
          defaultDate: self.value,
        })
        .change(function(e) {
          self.$emit('input', $(self.$el).data('datetimepicker').date);
        });
    $(this.$el).data('datetimepicker').setDate(this.value);
  },
};
</script>