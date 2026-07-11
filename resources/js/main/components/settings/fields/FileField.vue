<template>
  <div class="file-upload-placeholder p-4 border border-dashed border-slate-300 rounded-lg text-center bg-slate-50">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="32" height="32" class="mx-auto mb-2 text-slate-400"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
    <span class="text-sm font-semibold text-slate-600 block">File upload field</span>
    <span class="text-xs text-slate-400 block mt-1">This component is reserved for dynamic file uploads in a future release.</span>
  </div>
</template>

<script>
export default {
  name: 'FileField',
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
};
</script>
