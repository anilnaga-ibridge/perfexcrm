<template>
  <div class="forgot-container flex items-center justify-center px-4 py-6 sm:px-6 lg:px-8 min-h-screen">
    <!-- Claymorphic Background Decorators -->
    <div class="clay-bg-decor cloud-1"></div>
    <div class="clay-bg-decor cloud-2"></div>
    
    <!-- Left Plant -->
    <div class="clay-bg-decor plant-left">
      <div class="leaf leaf-1"></div>
      <div class="leaf leaf-2"></div>
      <div class="leaf leaf-3"></div>
      <div class="pot"></div>
    </div>
    
    <!-- Right Flower -->
    <div class="clay-bg-decor flower-right">
      <div class="flower-stem"></div>
      <div class="flower-petal petal-1"></div>
      <div class="flower-petal petal-2"></div>
      <div class="flower-petal petal-3"></div>
      <div class="flower-petal petal-4"></div>
      <div class="flower-petal petal-5"></div>
      <div class="flower-center"></div>
      <div class="pot"></div>
    </div>

    <!-- Main Two-Column Wrapper -->
    <div class="forgot-wrapper">
      
      <!-- Left Column: Holographic Mascot Graphic -->
      <div class="robot-display-column">
        <div class="robot-visual-wrapper">
          <div class="robot-mascot-container">
            <img 
              :src="crmHomeUrl" 
              alt="Security Hologram" 
              class="robot-mascot-img"
            />
          </div>
        </div>
      </div>

      <!-- Right Column: Forgot Password Luxury Card -->
      <div class="phone-card">
        <!-- Top Language Selector Badge -->
        <div class="lang-selector-pill">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20M2 12h20"/></svg>
          <span>EN</span>
          <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
        </div>

        <div class="phone-header">
          <div v-if="showLoginLogo" class="login-logo-brand-header mb-2 flex flex-col items-center justify-center">
            <template v-if="resolvedLogo">
              <img :src="resolvedLogo" alt="Brand Logo" class="login-brand-img" :style="{ maxWidth: loginLogoWidth }" />
            </template>
            <template v-else>
              <span class="login-brand-text text-slate-800 font-bold text-base tracking-wide mb-1">{{ loginLogoText }}</span>
            </template>
          </div>
          
          <h2 class="welcome-title">
            Forgot Password?
          </h2>
          <p class="welcome-subtitle">Enter your email and we'll send you a recovery link</p>
        </div>

        <!-- Key Shield Icon Badge -->
        <div class="avatar-container">
          <div class="clay-key-badge">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="36" height="36" class="text-indigo-600">
              <rect x="3" y="11" width="18" height="11" rx="4" ry="4"/>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
          </div>
        </div>

        <!-- Form Card -->
        <div class="form-card">
          <a-form
            v-if="!sent"
            layout="vertical"
            :model="form"
            @finish="handleForgot"
            class="forgot-form-element"
          >
            <!-- Email Field -->
            <a-form-item
              name="email"
              label="Email Address"
              :rules="[{ required: true, type: 'email', message: 'Please input a valid email address!' }]"
              class="clay-form-item"
            >
              <glass-input>
                <template #icon>
                  <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                  </svg>
                </template>
                <a-input
                  v-model:value="form.email"
                  placeholder="admin@test.com"
                >
                  <template #suffix>
                    <span class="input-dummy-suffix"></span>
                  </template>
                </a-input>
              </glass-input>
            </a-form-item>

            <!-- Submit Button -->
            <a-form-item class="mb-0 mt-2">
              <a-button
                type="primary"
                html-type="submit"
                size="large"
                :loading="loading"
                block
                class="login-btn"
                :style="dynamicBtnStyle"
              >
                <span class="login-btn-inner">
                  <span>Send Reset Link</span>
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </span>
              </a-button>
            </a-form-item>
          </a-form>

          <!-- Sent Success State -->
          <div v-else class="text-center py-4 space-y-3">
            <div class="w-14 h-14 bg-emerald-50 border border-emerald-200 rounded-full flex items-center justify-center mx-auto shadow-xs">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h4 class="text-base font-bold text-slate-800">Reset Email Sent!</h4>
            <p class="text-xs text-slate-500 max-w-xs mx-auto leading-relaxed">
              We've sent a password reset link to <strong class="text-slate-700">{{ form.email }}</strong>. Please check your inbox.
            </p>
            <button class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 underline mt-2" type="button" @click="sent = false">
              Didn't receive email? Try again
            </button>
          </div>

          <div class="text-center text-xs text-slate-500 font-medium mt-6 pt-4 border-t border-slate-200/60">
            <router-link :to="{ name: 'admin.login' }" class="back-link inline-flex items-center gap-1.5 font-bold text-slate-700 hover:text-indigo-600 transition-colors">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
              <span>Back to Login</span>
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent, reactive, ref, computed, onMounted } from 'vue';
import logoUrl from '../../assets/logo.png';
import crmHomeUrl from '../../assets/crmhome.png';
import axios from 'axios';
import { message } from 'ant-design-vue';
import GlassInput from '../../components/GlassInput.vue';

export default defineComponent({
  name: 'ForgotPassword',
  components: { GlassInput },
  setup() {
    const getLoginLogoSettings = () => {
      const saved = localStorage.getItem('crm_theme_style_settings');
      if (saved) {
        try { return JSON.parse(saved); } catch(e) {}
      }
      return {};
    };

    const loginLogoSettings = ref(getLoginLogoSettings());

    const resolvedLogo = computed(() => {
      if (loginLogoSettings.value.login_logo_url !== undefined) {
        return loginLogoSettings.value.login_logo_url;
      }
      return logoUrl;
    });

    const showLoginLogo = computed(() => {
      return loginLogoSettings.value.login_logo_show !== false;
    });

    const loginLogoText = computed(() => {
      return loginLogoSettings.value.login_logo_text || 'iBRIDGE DIGITAL CRM';
    });

    const loginLogoWidth = computed(() => {
      return loginLogoSettings.value.login_logo_width || '180px';
    });

    const loading = ref(false);
    const sent = ref(false);
    const form = reactive({ email: '' });

    const handleForgot = async () => {
      loading.value = true;
      try {
        const res = await axios.post('/auth/forgot-password', { email: form.email });
        sent.value = true;
        message.success(res.data?.message || 'Password reset link sent! Check your inbox.');
      } catch (err) {
        let msg = err.response?.data?.message;
        if (msg === 'passwords.throttled') {
          msg = 'Please wait a moment before requesting another password reset link.';
        } else if (msg === 'passwords.user') {
          msg = 'We could not find an account registered with that email address.';
        }
        message.error(msg || 'Failed to send reset link. Please try again.');
      } finally {
        loading.value = false;
      }
    };

    const dynamicBtnStyle = computed(() => {
      const primary = loginLogoSettings.value.btn_primary 
        || loginLogoSettings.value.admin_sidebar_active_bg 
        || loginLogoSettings.value.primary_color 
        || '#7367F0';
      return {
        background: primary,
        borderColor: primary,
        boxShadow: `0 8px 24px ${primary}4d`,
      };
    });

    onMounted(() => {
      const updateSettings = () => {
        loginLogoSettings.value = getLoginLogoSettings();
      };
      window.addEventListener('crm-theme-settings-updated', updateSettings);
      window.addEventListener('storage', updateSettings);
    });

    return { 
      form, 
      loading, 
      sent, 
      handleForgot, 
      resolvedLogo,
      showLoginLogo,
      loginLogoText,
      loginLogoWidth,
      crmHomeUrl,
      dynamicBtnStyle,
    };
  },
});
</script>

<style scoped>
/* ── Claymorphic UI Theme & Background ───────────────────────── */
.forgot-container {
  background: #b6c8bf; /* Soft Sage Green background */
  position: relative;
  overflow: hidden;
  font-family: 'Outfit', 'Inter', sans-serif;
  min-height: 100vh;
}

/* Background Puffy Clouds */
.clay-bg-decor {
  position: absolute;
  pointer-events: none;
}

.cloud-1 {
  top: 15%;
  left: 10%;
  width: 140px;
  height: 60px;
  background: #faf6f0;
  border-radius: 100px;
  box-shadow: 
    inset 6px 6px 12px rgba(255,255,255,0.9),
    5px 10px 20px rgba(70, 50, 110, 0.18);
  opacity: 0.9;
}
.cloud-1::before {
  content: '';
  position: absolute;
  top: -30px;
  left: 20px;
  width: 60px;
  height: 60px;
  background: #faf6f0;
  border-radius: 50%;
  box-shadow: inset 4px 4px 8px rgba(255,255,255,0.9);
}
.cloud-1::after {
  content: '';
  position: absolute;
  top: -20px;
  right: 25px;
  width: 50px;
  height: 50px;
  background: #faf6f0;
  border-radius: 50%;
  box-shadow: inset 4px 4px 8px rgba(255,255,255,0.9);
}

.cloud-2 {
  top: 25%;
  right: 8%;
  width: 120px;
  height: 50px;
  background: #faf6f0;
  border-radius: 100px;
  box-shadow: 
    inset 6px 6px 12px rgba(255,255,255,0.9),
    5px 10px 20px rgba(70, 50, 110, 0.18);
  opacity: 0.85;
}
.cloud-2::before {
  content: '';
  position: absolute;
  top: -25px;
  left: 20px;
  width: 50px;
  height: 50px;
  background: #faf6f0;
  border-radius: 50%;
  box-shadow: inset 4px 4px 8px rgba(255,255,255,0.9);
}

/* Left Plant & Right Flower */
.plant-left {
  bottom: 8%;
  left: 6%;
  width: 120px;
  height: 220px;
}
@media (max-width: 768px) {
  .plant-left, .cloud-1, .cloud-2, .flower-right, .robot-display-column {
    display: none !important;
  }
}

.pot {
  position: absolute;
  bottom: 0;
  left: 30px;
  width: 60px;
  height: 50px;
  background: #e59a7d;
  border-radius: 8px 8px 20px 20px;
  box-shadow: 
    inset 3px 3px 6px rgba(255,255,255,0.5),
    inset -3px -3px 6px rgba(0,0,0,0.15),
    3px 6px 12px rgba(70, 50, 110, 0.2);
}
.pot::before {
  content: '';
  position: absolute;
  top: -4px;
  left: -4px;
  width: 68px;
  height: 8px;
  background: #e59a7d;
  border-radius: 4px;
  box-shadow: 
    inset 2px 2px 4px rgba(255,255,255,0.5),
    1px 2px 4px rgba(0,0,0,0.1);
}
.leaf {
  position: absolute;
  background: #88a37b;
  border-radius: 50% 50% 10px 50%;
  box-shadow: 
    inset 3px 3px 6px rgba(255,255,255,0.4),
    2px 4px 8px rgba(0,0,0,0.1);
}
.leaf-1 { width: 40px; height: 60px; bottom: 50px; left: 20px; transform: rotate(-35deg); }
.leaf-2 { width: 45px; height: 65px; bottom: 85px; left: 55px; transform: scaleX(-1) rotate(-35deg); }
.leaf-3 { width: 35px; height: 55px; bottom: 130px; left: 30px; transform: rotate(-15deg); }

.flower-right {
  bottom: 8%;
  right: 6%;
  width: 120px;
  height: 220px;
}
.flower-stem {
  position: absolute;
  bottom: 45px;
  left: 58px;
  width: 6px;
  height: 100px;
  background: #88a37b;
  border-radius: 4px;
  box-shadow: inset 1px 1px 3px rgba(255,255,255,0.4);
}
.flower-center {
  position: absolute;
  bottom: 125px;
  left: 48px;
  width: 26px;
  height: 26px;
  background: #ecd278;
  border-radius: 50%;
  z-index: 10;
  box-shadow: inset 2px 2px 5px rgba(255,255,255,0.6), 1px 2px 4px rgba(0,0,0,0.15);
}
.flower-petal {
  position: absolute;
  width: 30px;
  height: 30px;
  background: #e8a7b0;
  border-radius: 50%;
  box-shadow: inset 2px 2px 5px rgba(255,255,255,0.4), 1px 2px 4px rgba(0,0,0,0.1);
}
.petal-1 { bottom: 138px; left: 46px; }
.petal-2 { bottom: 123px; left: 63px; }
.petal-3 { bottom: 108px; left: 52px; }
.petal-4 { bottom: 114px; left: 33px; }
.petal-5 { bottom: 132px; left: 30px; }

/* ── Two-Column Layout ───────────────────────────────────────────── */
.forgot-wrapper {
  display: flex;
  align-items: center;
  gap: 50px;
  position: relative;
  z-index: 10;
  max-width: 1140px;
  width: 100%;
  justify-content: center;
}

/* Left Column: Robot Mascot Display */
.robot-display-column {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  flex: 1.4;
  max-width: 580px;
}

.robot-visual-wrapper {
  position: relative;
  width: 100%;
  max-width: 550px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.robot-mascot-container {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  z-index: 5;
}

.robot-mascot-img {
  width: 100%;
  max-height: 520px;
  object-fit: contain;
  filter: drop-shadow(0 20px 45px rgba(0, 0, 0, 0.18));
  animation: float-hero-robot 5s ease-in-out infinite alternate;
}

@keyframes float-hero-robot {
  0% { transform: translateY(0px); }
  100% { transform: translateY(-12px); }
}

/* ── Phone Card ────────────────────────────────────────────────── */
.phone-card {
  position: relative;
  z-index: 10;
  width: 100%;
  max-width: 440px;
  background: #faf6f0;
  border: 2px solid rgba(255, 255, 255, 0.6);
  border-radius: 40px;
  padding: 28px 26px;
  box-shadow: 
    0 30px 60px rgba(60, 50, 100, 0.2),
    inset 4px 4px 10px rgba(255, 255, 255, 0.85);
  display: flex;
  flex-direction: column;
  align-items: center;
}

.lang-selector-pill {
  position: absolute;
  top: 18px;
  right: 18px;
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  background: #ffffff;
  border: 1px solid rgba(226, 232, 240, 0.9);
  border-radius: 14px;
  font-size: 11px;
  font-weight: 600;
  color: #475569;
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
}

.phone-header {
  text-align: center;
  margin-bottom: 8px;
}
.welcome-title {
  font-size: 24px;
  font-weight: 800;
  color: #3d4d46;
  margin: 0;
}
.welcome-subtitle {
  font-size: 12.5px;
  color: #5c6e66;
  font-weight: 500;
  margin-top: 4px;
}

.avatar-container {
  position: relative;
  width: 100%;
  display: flex;
  justify-content: center;
  margin-top: 10px;
  margin-bottom: 12px;
}

.clay-key-badge {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  background: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 
    inset 2px 2px 6px rgba(255, 255, 255, 0.9),
    0 10px 20px rgba(99, 102, 241, 0.15);
  border: 2px solid rgba(226, 232, 240, 0.8);
}

.form-card {
  width: 100%;
}

.clay-form-item {
  margin-bottom: 16px;
}

:deep(.ant-form-item-label > label) {
  font-weight: 700 !important;
  color: #3d4d46 !important;
  font-size: 12.5px !important;
}

.input-dummy-suffix {
  display: inline-block;
  width: 14px;
}

.login-btn {
  border: none !important;
  border-radius: 12px !important;
  height: 48px !important;
  font-weight: 700 !important;
  font-size: 15px !important;
  color: #ffffff !important;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  padding: 0 16px !important;
  overflow: hidden !important;
}

.login-btn :deep(> span) {
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  width: auto !important;
}

.login-btn-inner {
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  gap: 8px !important;
  width: 100% !important;
}

.login-btn-inner svg {
  flex-shrink: 0 !important;
  display: inline-block !important;
  vertical-align: middle !important;
  transition: transform 0.2s ease !important;
}

.login-btn:hover {
  transform: translateY(-2px);
  filter: brightness(1.06);
}

.login-btn:hover .login-btn-inner svg {
  transform: translateX(3px);
}

.login-btn:active {
  transform: translateY(0px);
}
</style>
