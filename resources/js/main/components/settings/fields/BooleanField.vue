<template>
  <a-switch
    :checked="isChecked"
    @change="onChange"
    :disabled="field.readonly || false"
  />
</template>

<script>
import { computed } from 'vue';

export default {
  name: 'BooleanField',
  props: {
    field: {
      type: Object,
      required: true,
    },
    value: {
      type: [Boolean, Number, String],
      default: false,
    },
  },
  emits: ['update:value'],
  setup(props, { emit }) {
    const isChecked = computed(() => {
      return props.value === true || props.value === 1 || props.value === '1' || props.value === 'true';
    });

    const onChange = (checked) => {
      emit('update:value', checked);
    };

    return {
      isChecked,
      onChange,
    };
  },
};
</script>
