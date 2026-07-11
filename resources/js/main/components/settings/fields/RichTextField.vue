<template>
  <div class="richtext-field-wrapper">
    <a-textarea
      :value="value"
      @input="onInput"
      :placeholder="field.placeholder || 'Enter HTML or rich content...'"
      :disabled="field.readonly || false"
      :rows="6"
    />
    <span class="text-xs text-slate-400 mt-1 block">WYSIWYG Rich Text Editor (Text area mode active. Full toolbar editor is reserved for a future release).</span>
  </div>
</template>

<script>
export default {
  name: 'RichTextField',
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
