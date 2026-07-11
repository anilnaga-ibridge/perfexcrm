<template>
  <a-textarea
    :value="value"
    @input="onInput"
    :placeholder="field.placeholder || ''"
    :disabled="field.readonly || false"
    :rows="4"
  />
</template>

<script>
export default {
  name: 'TextAreaField',
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
    const onInput = (e) => {
      emit('update:value', e.target.value);
    };
    return {
      onInput,
    };
  },
};
</script>
