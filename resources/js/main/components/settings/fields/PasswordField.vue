<template>
  <a-input-password
    :value="value"
    @input="onInput"
    :placeholder="field.placeholder || ''"
    :disabled="field.readonly || false"
  />
</template>

<script>
export default {
  name: 'PasswordField',
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
