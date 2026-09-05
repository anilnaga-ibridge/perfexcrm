<template>
  <div class="glass-input-wrapper">
    <div class="glass-input-badge">
      <slot name="icon"></slot>
    </div>
    <slot></slot>
  </div>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'GlassInput',
});
</script>

<style scoped>
.glass-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.glass-input-badge {
  position: absolute;
  left: 6px;
  top: 50%;
  transform: translateY(-50%);
  width: 32px;
  height: 32px;
  background: #bcb3e2;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 5;
  pointer-events: none;
  box-shadow:
    inset 1px 1px 3px rgba(255, 255, 255, 0.5),
    1px 2px 4px rgba(0, 0, 0, 0.05);
}

.glass-input-badge :deep(svg) {
  width: 15px;
  height: 15px;
}

/* ── Shared input styles (a-input + a-input-password) ─────── */
:deep(.ant-input) {
  background: #ffffff !important;
  border: 1px solid transparent !important;
  border-radius: 999px !important;
  color: #5f4f8d !important;
  padding: 11px 20px 11px 48px !important;
  height: 46px !important;
  box-shadow:
    inset 3px 3px 6px rgba(100, 90, 130, 0.12),
    inset -3px -3px 6px rgba(255, 255, 255, 0.9),
    0 2px 4px rgba(100, 90, 130, 0.03) !important;
  transition: all 0.3s ease !important;
  font-weight: 600;
  font-size: 14px !important;
}

:deep(.ant-input-affix-wrapper) {
  background: #ffffff !important;
  border: 1px solid transparent !important;
  border-radius: 999px !important;
  padding: 0 20px 0 48px !important;
  height: 46px !important;
  box-shadow:
    inset 3px 3px 6px rgba(100, 90, 130, 0.12),
    inset -3px -3px 6px rgba(255, 255, 255, 0.9),
    0 2px 4px rgba(100, 90, 130, 0.03) !important;
  transition: all 0.3s ease !important;
  display: flex !important;
  align-items: center !important;
}

:deep(.ant-input-affix-wrapper .ant-input) {
  background: transparent !important;
  border: none !important;
  padding: 0 !important;
  box-shadow: none !important;
  height: auto !important;
  flex: 1 !important;
  min-width: 0 !important;
  font-size: 14px !important;
  font-weight: 600;
  color: #5f4f8d !important;
}

:deep(.ant-input:hover),
:deep(.ant-input:focus),
:deep(.ant-input-affix-wrapper:hover),
:deep(.ant-input-affix-wrapper-focused) {
  box-shadow:
    inset 3px 3px 6px rgba(100, 90, 130, 0.14),
    inset -3px -3px 6px rgba(255, 255, 255, 0.95),
    0 0 0 3px rgba(159, 142, 214, 0.3) !important;
}

:deep(.ant-input-password-icon) {
  color: #8c7fb2 !important;
  right: 14px !important;
}

:deep(.ant-input-suffix) {
  margin: 0 !important;
  padding: 0 !important;
}

.input-dummy-suffix {
  display: inline-block;
  width: 14px;
}

:deep(.ant-input::placeholder) {
  color: #a59ab8 !important;
}
</style>
