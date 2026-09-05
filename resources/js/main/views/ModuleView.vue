<template>
  <div class="module-view-container font-['Public_Sans',sans-serif]">
    <!-- Native Vue Page -->
    <div v-if="nativeComponent" class="native-module-page">
      <component :is="nativeComponent" :key="componentKey" />
    </div>

    <!-- SSO Iframe Fallback -->
    <div v-else-if="useSso" class="iframe-card bg-white border border-[#EBE9F1] rounded-lg shadow-sm overflow-hidden">
      <a-spin :spinning="loadingSso" size="large" tip="Loading module workspace..." style="height: 100%; width: 100%;">
        <iframe
          v-if="iframeSrc"
          :src="iframeSrc"
          class="module-iframe"
          frameborder="0"
          allow="camera; microphone; geolocation; payment"
          @load="onIframeLoaded"
        ></iframe>
        <div v-else-if="!loadingSso" class="placeholder-content p-12 text-center text-[#82868B]">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48" class="mx-auto mb-2 text-[#7367F0]"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
          <p class="text-sm font-semibold text-[#4B465C]">Module workspace loaded</p>
          <p class="text-xs text-[#82868B] mt-1">If page does not appear, click reload below.</p>
          <button type="button" class="mt-3 px-3.5 py-1.5 bg-[#7367F0] text-white text-xs font-semibold rounded-md border-none cursor-pointer" @click="resolvePage">
            Reload Workspace
          </button>
        </div>
      </a-spin>
    </div>

    <!-- Neither native nor SSO available while loading -->
    <div v-else class="placeholder-content p-12 text-center text-[#82868B]">
      <a-spin size="large" tip="Initializing module..." />
    </div>
  </div>
</template>

<script>
import { ref, computed, watch, shallowRef } from 'vue';
import { useRoute } from 'vue-router';
import { useModuleStore } from '../store/moduleStore';
import { resolveModulePage } from '../modules/ModulePageLoader';
import GenericModuleSettings from '../components/GenericModuleSettings.vue';
import axios from 'axios';

export default {
  name: 'ModuleView',
  setup() {
    const route = useRoute();
    const moduleStore = useModuleStore();

    const slug = computed(() => route.params.slug);
    const pagePath = computed(() => {
      const p = route.params.pathMatch;
      return Array.isArray(p) ? p.join('/') : (p || '');
    });

    const nativeComponent = shallowRef(null);
    const useSso = ref(false);
    const componentKey = ref(0);

    const loadingSso = ref(false);
    const iframeSrc = ref('');
    let ssoTimeout = null;

    const onIframeLoaded = () => {
      loadingSso.value = false;
      if (ssoTimeout) clearTimeout(ssoTimeout);
    };

    const resolvePage = async () => {
      const activeSlug = slug.value;
      const path = pagePath.value;
      if (!activeSlug) return;

      nativeComponent.value = null;
      useSso.value = false;
      loadingSso.value = false;
      iframeSrc.value = '';
      if (ssoTimeout) clearTimeout(ssoTimeout);

      const resolved = resolveModulePage(activeSlug, path);
      if (resolved) {
        nativeComponent.value = resolved;
        componentKey.value++;
      } else {
        // Find if this is the dynamic settings page
        const currentModule = moduleStore.modules.find(m => m.slug === activeSlug);
        if (currentModule && (
            (currentModule.settings_route && path === currentModule.settings_route) ||
            (currentModule.has_settings && path === 'settings')
        )) {
          nativeComponent.value = GenericModuleSettings;
          componentKey.value++;
        } else {
          useSso.value = true;
          await setupSso();
        }
      }
    };

    const setupSso = async () => {
      const activeSlug = slug.value;
      const path = pagePath.value || 'dashboard';
      if (!activeSlug) {
        iframeSrc.value = '';
        return;
      }

      loadingSso.value = true;
      ssoTimeout = setTimeout(() => {
        loadingSso.value = false;
      }, 4000);

      try {
        const redirect = `/plugins/${activeSlug}/${path}`;
        const response = await axios.get('/modules/sso-url', {
          params: { redirect }
        });
        if (response.data?.url) {
          iframeSrc.value = response.data.url;
        } else {
          // Direct fallback URL
          iframeSrc.value = `/plugins/${activeSlug}/${path}`;
        }
      } catch (err) {
        // Direct fallback
        iframeSrc.value = `/plugins/${activeSlug}/${path}`;
      } finally {
        setTimeout(() => {
          loadingSso.value = false;
        }, 1500);
      }
    };

    watch(
      () => [route.params.slug, route.params.pathMatch],
      resolvePage,
      { immediate: true }
    );

    const openInNewTab = () => {
      if (iframeSrc.value) {
        window.open(iframeSrc.value, '_blank');
      }
    };

    return {
      nativeComponent,
      useSso,
      componentKey,
      iframeSrc,
      loadingSso,
      onIframeLoaded,
      resolvePage,
      openInNewTab,
    };
  },
};
</script>

<style scoped>
.module-view-container {
  display: flex;
  flex-direction: column;
  height: 100%;
}
.native-module-page {
  flex: 1;
}
.iframe-card {
  flex: 1;
  height: calc(100vh - 130px);
  position: relative;
}
.module-iframe {
  width: 100%;
  height: 100%;
  border: none;
  background: #fff;
}
:deep(.ant-spin-nested-loading),
:deep(.ant-spin-container) {
  height: 100% !important;
}
.placeholder-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
}
</style>
