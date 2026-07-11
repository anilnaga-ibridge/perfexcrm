<template>
  <a-select
    :value="resolvedValue"
    @change="onChange"
    :placeholder="field.placeholder || 'Select option...'"
    :disabled="field.readonly || false"
    :mode="isMultiple ? 'multiple' : undefined"
    style="width: 100%"
  >
    <a-select-option v-for="opt in options" :key="opt" :value="opt">
      {{ opt }}
    </a-select-option>
  </a-select>
</template>

<script>
import { computed } from 'vue';

export default {
  name: 'SelectField',
  props: {
    field: {
      type: Object,
      required: true,
    },
    value: {
      type: [String, Array],
      default: undefined,
    },
  },
  emits: ['update:value'],
  setup(props, { emit }) {
    const isMultiple = computed(() => {
      return props.field.type === 'multiselect';
    });

    const options = computed(() => {
      return props.field.validation?.options || [];
    });

    const resolvedValue = computed(() => {
      if (isMultiple.value) {
        if (Array.isArray(props.value)) return props.value;
        if (typeof props.value === 'string') {
          try {
            const parsed = JSON.parse(props.value);
            return Array.isArray(parsed) ? parsed : [];
          } catch (_) {
            return props.value ? [props.value] : [];
          }
        }
        return [];
      }
      return props.value;
    });

    const onChange = (val) => {
      emit('update:value', val);
    };

    return {
      isMultiple,
      options,
      resolvedValue,
      onChange,
    };
  },
};
</script>
