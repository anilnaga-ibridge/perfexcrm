<template>
  <div class="color-field-container flex items-center gap-2">
    <input
      type="color"
      :value="colorVal"
      @input="onColorInput"
      :disabled="field.readonly || false"
      class="color-picker-input cursor-pointer border border-slate-200 rounded p-0 bg-transparent w-10 h-8"
      style="width: 40px; height: 32px; padding: 0; border: 1px solid #cbd5e1; border-radius: 6px;"
    />
    <a-input
      :value="value"
      @input="onTextInput"
      :placeholder="field.placeholder || '#ffffff'"
      :disabled="field.readonly || false"
      style="width: 130px"
    />
  </div>
</template>

<script>
import { computed } from 'vue';

export default {
  name: 'ColorField',
  props: {
    field: {
      type: Object,
      required: true,
    },
    value: {
      type: String,
      default: '#ffffff',
    },
  },
  emits: ['update:value'],
  setup(props, { emit }) {
    const colorVal = computed(() => {
      const v = props.value || '#ffffff';
      // Ensure hex format is valid for HTML color picker
      return /^#[0-9A-F]{6}$/i.test(v) ? v : '#ffffff';
    });

    const onColorInput = (e) => {
      emit('update:value', e.target.value);
    };

    const onTextInput = (e) => {
      emit('update:value', e.target.value);
    };

    return {
      colorVal,
      onColorInput,
      onTextInput,
    };
  },
};
</script>
