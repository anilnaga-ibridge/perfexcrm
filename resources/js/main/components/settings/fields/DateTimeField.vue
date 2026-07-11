<template>
  <a-date-picker
    :value="value || undefined"
    @change="onChange"
    show-time
    value-format="YYYY-MM-DD HH:mm:ss"
    :placeholder="field.placeholder || 'Select date and time...'"
    :disabled="field.readonly || false"
    style="width: 100%"
  />
</template>

<script>
export default {
  name: 'DateTimeField',
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
