<template>
  <a-input-number
    :value="value"
    @change="onChange"
    :placeholder="field.placeholder || ''"
    :disabled="field.readonly || false"
    :min="field.validation?.min"
    :max="field.validation?.max"
    style="width: 100%"
  />
</template>

<script>
export default {
  name: 'NumberField',
  props: {
    field: {
      type: Object,
      required: true,
    },
    value: {
      type: Number,
      default: null,
    },
  },
  emits: ['update:value'],
  setup(props, { emit }) {
    const onChange = (val) => {
      emit('update:value', val);
    };
    return {
      onChange,
    };
  },
};
</script>
