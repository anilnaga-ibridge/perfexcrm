<template>
  <div class="image-upload-placeholder p-4 border border-dashed border-slate-300 rounded-lg text-center bg-slate-50">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="32" height="32" class="mx-auto mb-2 text-slate-400"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
    <span class="text-sm font-semibold text-slate-600 block">Image upload field</span>
    <span class="text-xs text-slate-400 block mt-1">This component is reserved for dynamic image uploads in a future release.</span>
  </div>
</template>

<script>
export default {
  name: 'ImageField',
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
