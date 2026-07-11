<template>
  <div class="markdown-field-wrapper">
    <a-textarea
      :value="value"
      @input="onInput"
      :placeholder="field.placeholder || 'Write markdown here...'"
      :disabled="field.readonly || false"
      :rows="6"
      style="font-family: monospace;"
    />
    <span class="text-xs text-slate-400 mt-1 block">Markdown Editor (Raw Text mode active. Rich preview is reserved for a future release).</span>
  </div>
</template>

<script>
export default {
  name: 'MarkdownField',
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
