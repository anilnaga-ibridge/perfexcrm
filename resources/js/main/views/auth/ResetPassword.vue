<template>
  <div class="reset-container flex items-center justify-center px-4 py-6 sm:px-6 lg:px-8 min-h-screen">
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
    <div class="reset-wrapper">
      
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

      <!-- Right Column: Reset Password Luxury Card -->
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
            Reset Password 🔐
          </h2>
          <p class="welcome-subtitle">Set a strong new password for your account</p>
        </div>

        <!-- Lock Shield Icon Badge -->
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
            layout="vertical"
            :model="form"
            @finish="handleReset"
            class="reset-form-element"
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

            <!-- New Password Field -->
            <a-form-item
              name="password"
              label="New Password"
              :rules="[{ required: true, min: 8, message: 'Password must be at least 8 characters!' }]"
              class="clay-form-item"
            >
              <glass-input>
                <template #icon>
                  <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                  </svg>
                </template>
                <a-input-password
                  v-model:value="form.password"
                  placeholder="New password (min. 8 chars)"
                />
              </glass-input>
            </a-form-item>

            <!-- Confirm Password Field -->
            <a-form-item
              name="password_confirmation"
              label="Confirm Password"
              :rules="confirmPasswordRules"
              class="clay-form-item"
            >
              <glass-input>
                <template #icon>
                  <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                  </svg>
                </template>
                <a-input-password
                  v-model:value="form.password_confirmation"
                  placeholder="Confirm new password"
                />
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
                  <span>Reset Password</span>
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </span>
              </a-button>
            </a-form-item>
          </a-form>

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
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { message } from 'ant-design-vue';
import GlassInput from '../../components/GlassInput.vue';

export default defineComponent({
  name: 'ResetPassword',
  components: { GlassInput },
  setup() {
    const route = useRoute();
    const router = useRouter();

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

    const loading = ref(false);
    const form = reactive({
      email: route.query.email || '',
      password: '',
      password_confirmation: '',
      token: route.params.token || '',
    });

    const confirmPasswordRules = [
      { required: true, message: 'Please confirm your password!' },
      {
        validator: async (_rule, value) => {
          if (value && value !== form.password) {
            return Promise.reject('Passwords do not match!');
          }
          return Promise.resolve();
        },
      },
    ];

    const handleReset = async () => {
      loading.value = true;
      try {
        const res = await axios.post('/auth/reset-password', { ...form });
        message.success(res.data?.message || 'Password reset successfully!');
        router.push({ name: 'admin.login' });
      } catch (err) {
        let msg = err.response?.data?.message;
        if (msg === 'passwords.token') {
          msg = 'This password reset link is invalid or has expired. Please request a new link.';
        } else if (msg === 'passwords.user') {
          msg = 'We could not find an account associated with this email address.';
        } else if (msg === 'passwords.throttled') {
          msg = 'Please wait a moment before trying again.';
        }
        message.error(msg || 'Failed to reset password. Please try again.');
      } finally {
        loading.value = false;
      }
    };

    return { 
      form, 
      loading, 
      handleReset, 
      confirmPasswordRules, 
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
.reset-container {
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
.reset-wrapper {
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
