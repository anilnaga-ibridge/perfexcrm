<template>
  <a-date-picker
    :value="value || undefined"
    @change="onChange"
    value-format="YYYY-MM-DD"
    :placeholder="field.placeholder || 'Select date...'"
    :disabled="field.readonly || false"
    style="width: 100%"
  />
</template>

<script>
export default {
  name: 'DateField',
  props: {
    field: {
      type: Object,
      required: true,
    },
    value: {
      type: String,
      default: '',
    },
  },
  emits: ['update:value'],
  setup(props, { emit }) {
    const onChange = (date, dateString) => {
      emit('update:value', dateString);
    };
    return {
      onChange,
    };
  },
};
</script>
