<template>
  <div class="login-container flex items-center justify-center px-4 py-6 sm:px-6 lg:px-8">
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
    <div class="login-wrapper">
      
      <!-- Left Column: Holographic Robot Mascot Graphic (Visible on Desktop) -->
      <div v-if="showLoginMascot" class="robot-display-column">
        <div class="robot-visual-wrapper">
          <!-- Full Un-cropped Robot Image Container -->
          <div class="robot-mascot-container">
            <img 
              :src="resolvedMascotUrl" 
              alt="Robotic Hologram" 
              class="robot-mascot-img"
            />
          </div>
        </div>
      </div>

      <!-- Right Column: Login Card -->
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
            Welcome Back  
          </h2>
          <p class="welcome-subtitle">Sign in to continue to your dashboard</p>
        </div>

        <!-- Avatar Character Container (Clay Image Avatar) -->
        <div v-if="showFormAvatar" class="avatar-container">
          <img :src="resolvedFormAvatarUrl" alt="Avatar" class="clay-avatar-img" />
        </div>

        <!-- Form Card -->
        <div class="form-card">
          <a-form
            layout="vertical"
            :model="loginForm"
            @finish="handleLogin"
            class="login-form-element"
          >
            <!-- Email Field -->
            <a-form-item
              name="email"
              :rules="[{ required: true, type: 'email', message: 'Please input a valid email address!' }]"
              class="clay-form-item"
            >
              <glass-input>
                <template #icon>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18" class="text-slate-400">
                    <rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-10 7L2 7"/>
                  </svg>
                </template>
                <a-input
                  v-model:value="loginForm.email"
                  placeholder="admin@test.com"
                >
                  <template #suffix>
                    <span class="input-dummy-suffix"></span>
                  </template>
                </a-input>
              </glass-input>
            </a-form-item>

            <!-- Password Field -->
            <a-form-item
              name="password"
              :rules="[{ required: true, message: 'Please input your password!' }]"
              class="clay-form-item"
            >
              <glass-input>
                <template #icon>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18" class="text-slate-400">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                  </svg>
                </template>
                <a-input-password
                  v-model:value="loginForm.password"
                  placeholder="••••••••"
                />
              </glass-input>
            </a-form-item>

            <!-- Remember Me + Forgot Password -->
            <div class="flex items-center justify-between mb-4 px-1 text-xs">
              <a-checkbox v-model:checked="loginForm.remember" class="text-slate-600 font-medium">Remember me</a-checkbox>
              <router-link :to="{ name: 'admin.forgot-password' }" class="forgot-link font-semibold text-indigo-600 hover:text-indigo-700">Forgot Password?</router-link>
            </div>

            <!-- Submit Button -->
            <a-form-item class="mb-0">
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
                  <span>Login</span>
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </span>
              </a-button>
            </a-form-item>
          </a-form>

          <div class="or-divider mt-4">
            <div class="line"></div>
            <span class="or-divider-text text-slate-400 text-xs font-medium px-2">or continue with</span>
            <div class="line"></div>
          </div>

          <!-- Social Buttons Row -->
          <div class="social-buttons-row flex items-center justify-center gap-3 mt-3">
            <button class="social-icon-btn" type="button" title="Google" @click="loginWithSocial('google')">
              <svg class="social-icon-svg" viewBox="0 0 24 24" width="18" height="18">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
              </svg>
            </button>
            <button class="social-icon-btn" type="button" title="Apple" @click="loginWithSocial('apple')">
              <svg class="social-icon-svg" viewBox="0 0 24 24" fill="#000000" width="18" height="18">
                <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 4.17c.66-.81 1.11-1.93.99-3.06-1 .04-2.21.67-2.93 1.49-.62.69-1.16 1.84-1.01 2.96 1.12.09 2.27-.57 2.95-1.39z"/>
              </svg>
            </button>
            <button class="social-icon-btn" type="button" title="Microsoft" @click="loginWithSocial('microsoft')">
              <svg class="social-icon-svg" viewBox="0 0 23 23" width="18" height="18">
                <path fill="#f35325" d="M1 1h10v10H1z"/>
                <path fill="#81bc06" d="M12 1h10v10H1z"/>
                <path fill="#05a6f0" d="M1 12h10v10H1z"/>
                <path fill="#ffba08" d="M12 12h10v10H1z"/>
              </svg>
            </button>
            <button class="social-icon-btn" type="button" title="Facebook" @click="loginWithSocial('facebook')">
              <svg class="social-icon-svg" viewBox="0 0 24 24" fill="#1877F2" width="18" height="18">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent, reactive, ref, computed, onMounted } from 'vue';
import logoUrl from '../../assets/logo.png';
import robotHologramUrl from '../../assets/robot_hologram.png';
import crmHomeUrl from '../../assets/crmhome.png';
import clayAvatarUrl from '../../assets/clay_avatar.png';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../../store/authStore';
import { message } from 'ant-design-vue';
import GlassInput from '../../components/GlassInput.vue';
import { SocialAuth } from '../../services/SocialAuth';

export default defineComponent({
  name: 'Login',
  components: { GlassInput },
  setup() {
    const router = useRouter();
    const route = useRoute();
    const authStore = useAuthStore();

    const getLoginLogoSettings = () => {
      const saved = localStorage.getItem('crm_theme_style_settings');
      if (saved) {
        try {
          const parsed = JSON.parse(saved);
          if (parsed.login_mascot_show === false && !parsed._mascot_user_explicit) {
            parsed.login_mascot_show = true;
            localStorage.setItem('crm_theme_style_settings', JSON.stringify(parsed));
          }
          return parsed;
        } catch(e) {}
      }
      return { login_mascot_show: true };
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

    const showLoginMascot = computed(() => {
      if (loginLogoSettings.value.login_mascot_show === false && loginLogoSettings.value._mascot_user_explicit) {
        return false;
      }
      return true;
    });

    const enableMascotPulse = computed(() => {
      return loginLogoSettings.value.login_mascot_pulse !== false;
    });

    const resolvedMascotUrl = computed(() => {
      return loginLogoSettings.value.login_mascot_url || robotHologramUrl;
    });

    const mascotStatusText = computed(() => {
      if (loginLogoSettings.value.login_mascot_status_text) {
        return loginLogoSettings.value.login_mascot_status_text;
      }
      const cName = loginLogoSettings.value.company_name || 'IBRIDGE DIGITAL';
      return `${cName.toUpperCase()} ONLINE`;
    });

    const showFormAvatar = computed(() => {
      return loginLogoSettings.value.login_avatar_show !== false;
    });

    const resolvedFormAvatarUrl = computed(() => {
      return loginLogoSettings.value.login_avatar_url || clayAvatarUrl;
    });

    const loading = ref(false);
    const loginForm = reactive({
      email: 'admin@test.com', // Pre-fill like the demo
      password: 'admin',
      remember: true,
    });

    onMounted(async () => {
      loginLogoSettings.value = getLoginLogoSettings();
      if (typeof window !== 'undefined' && window.addEventListener) {
        window.addEventListener('crm-theme-settings-updated', (evt) => {
          if (evt.detail) {
            loginLogoSettings.value = evt.detail;
          }
        });
      }

      // Handle OAuth callback token exchange
      if (route.query.social_callback) {
        loading.value = true;
        try {
          const result = await authStore.socialExchangeAction();
          if (result.success) {
            message.success('Welcome back!');
            router.push({ name: 'admin.dashboard' });
          } else {
            message.error(result.message || 'Social login failed');
          }
        } catch (e) {
          message.error('Failed to complete social login');
        } finally {
          loading.value = false;
        }
      }

      // Handle query errors (like deactivated account, etc.)
      if (route.query.error) {
        message.error(decodeURIComponent(route.query.error));
      }
    });

    const handleLogin = async () => {
      loading.value = true;
      try {
        const result = await authStore.loginAction(loginForm);
        if (result.success) {
          message.success('Login successful! Welcome to iBridge CRM.');
          router.push({ name: 'admin.dashboard' });
        } else {
          message.error(result.message || 'Invalid email or password.');
        }
      } catch (err) {
        message.error('An error occurred during login. Please try again.');
      } finally {
        loading.value = false;
      }
    };

    const loginWithSocial = (provider) => {
      SocialAuth.login(provider);
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
      loginForm,
      loading,
      handleLogin,
      loginWithSocial,
      resolvedLogo,
      showLoginLogo,
      loginLogoText,
      loginLogoWidth,
      showLoginMascot,
      enableMascotPulse,
      resolvedMascotUrl,
      mascotStatusText,
      showFormAvatar,
      resolvedFormAvatarUrl,
      robotHologramUrl,
      crmHomeUrl,
      clayAvatarUrl,
      dynamicBtnStyle,
    };
  },
});
</script>

<style scoped>
/* ── Claymorphic UI Theme & Background ───────────────────────── */
.login-container {
  background: #ffffff !important;
  position: relative;
  overflow: hidden;
  font-family: 'Outfit', 'Inter', sans-serif;
  height: 100vh;
  max-height: 100vh;
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

/* Left Plant */
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
  background: #e59a7d; /* Terracotta pot */
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
  background: #88a37b; /* Clay green */
  border-radius: 50% 50% 10px 50%;
  box-shadow: 
    inset 3px 3px 6px rgba(255,255,255,0.4),
    2px 4px 8px rgba(0,0,0,0.1);
}
.leaf-1 {
  width: 40px;
  height: 60px;
  bottom: 50px;
  left: 20px;
  transform: rotate(-35deg);
}
.leaf-2 {
  width: 45px;
  height: 65px;
  bottom: 85px;
  left: 55px;
  transform: scaleX(-1) rotate(-35deg);
}
.leaf-3 {
  width: 35px;
  height: 55px;
  bottom: 130px;
  left: 30px;
  transform: rotate(-15deg);
}

/* Right Flower */
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
  background: #ecd278; /* yellow center */
  border-radius: 50%;
  z-index: 10;
  box-shadow: 
    inset 2px 2px 5px rgba(255,255,255,0.6),
    1px 2px 4px rgba(0,0,0,0.15);
}
.flower-petal {
  position: absolute;
  width: 30px;
  height: 30px;
  background: #e8a7b0; /* soft pink petals */
  border-radius: 50%;
  box-shadow: 
    inset 2px 2px 5px rgba(255,255,255,0.4),
    1px 2px 4px rgba(0,0,0,0.1);
}
.petal-1 { bottom: 138px; left: 46px; }
.petal-2 { bottom: 123px; left: 63px; }
.petal-3 { bottom: 108px; left: 52px; }
.petal-4 { bottom: 114px; left: 33px; }
.petal-5 { bottom: 132px; left: 30px; }

/* ── Two-Column Layout ───────────────────────────────────────────── */
.login-wrapper {
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

.robot-hero-details {
  margin-top: 14px;
  width: 100%;
}

.hero-main-title {
  font-size: 26px;
  font-weight: 900;
  line-height: 1.25;
  color: #1e293b;
  margin: 0 0 4px 0;
  letter-spacing: -0.02em;
}

.hero-gradient-text {
  background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #d946ef 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.hero-subtext {
  font-size: 13px;
  font-weight: 500;
  color: #64748b;
  margin: 0 0 14px 0;
}

.hero-feature-pills {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
  margin-bottom: 14px;
}

.feature-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(226, 232, 240, 0.9);
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
  color: #334155;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
}



.robot-status {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 5px 14px;
  background: rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(226, 232, 240, 0.9);
  border-radius: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}
.status-pulse {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #10b981;
  animation: status-glow 1.5s ease-in-out infinite alternate;
}
@keyframes status-glow {
  0% { transform: scale(0.9); opacity: 0.5; box-shadow: 0 0 0 0px rgba(16, 185, 129, 0.5); }
  100% { transform: scale(1.1); opacity: 1; box-shadow: 0 0 8px 3px rgba(16, 185, 129, 0.4); }
}
.status-text {
  font-family: 'Inter', sans-serif;
  font-size: 11px;
  color: #475569;
  letter-spacing: 0.05em;
  font-weight: 700;
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
  padding: 24px 24px 20px 24px;
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
  transition: all 0.2s ease;
}
.lang-selector-pill:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
}

/* Phone Header */
.phone-header {
  text-align: center;
  margin-bottom: 5px;
}
.heart-container {
  display: flex;
  justify-content: center;
  margin-bottom: 2px;
  animation: beat 1.2s infinite alternate;
}
@keyframes beat {
  to { transform: scale(1.15); }
}
.heart-icon {
  font-size: 24px;
}
.welcome-title {
  font-size: 28px;
  font-weight: 800;
  color: #3d4d46;
  margin: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}
.welcome-subtitle {
  font-size: 13.5px;
  color: #5c6e66;
  font-weight: 600;
  margin-top: 2px;
}
.sparkle {
  font-size: 16px;
  color: #ecd278;
}

/* ── Clay Character Avatar ──────────────────────────────────────── */
.avatar-container {
  position: relative;
  width: 100%;
  height: 120px;
  display: flex;
  justify-content: center;
  margin-top: 5px;
  margin-bottom: -20px;
  z-index: 15;
  overflow: visible;
  pointer-events: none;
}

.clay-avatar-img {
  width: 140px;
  height: 140px;
  object-fit: contain;
  transform: translateY(-20px);
  filter: drop-shadow(0 10px 20px rgba(60, 50, 100, 0.15));
  mix-blend-mode: multiply;
}

/* ── Overlapping Form Card ────────────────────────────────────── */
.form-card {
  position: relative;
  z-index: 20;
  width: 100%;
  background: #faf6f0;
  border-radius: 36px;
  padding: 25px 22px 15px 22px;
  box-shadow: 
    0 15px 30px rgba(60, 50, 100, 0.12),
    inset 3px 3px 8px rgba(255, 255, 255, 0.85);
  border: 1px solid rgba(255, 255, 255, 0.4);
}

.clay-form-item {
  margin-bottom: 12px !important;
}

/* Checkbox overrides */
:deep(.ant-checkbox-wrapper) {
  color: #5c6e66 !important;
  font-size: 13.5px;
  font-weight: 700;
}

:deep(.ant-checkbox-inner) {
  background-color: #ffffff !important;
  border: 1px solid rgba(87, 128, 112, 0.3) !important;
  box-shadow: 
    inset 1px 1px 3px rgba(30, 45, 38, 0.1),
    inset -1px -1px 3px rgba(255, 255, 255, 0.8) !important;
  border-radius: 6px !important;
}

:deep(.ant-checkbox-checked .ant-checkbox-inner) {
  background-color: #cc805c !important;
  border-color: #cc805c !important;
  box-shadow: none !important;
}

:deep(.ant-checkbox-checked::after) {
  border-color: #cc805c !important;
}

/* Links & Buttons */
.forgot-link {
  color: #5c6e66;
  font-size: 13.5px;
  font-weight: 700;
  transition: all 0.2s;
}
.forgot-link:hover {
  color: #cc805c;
}

.register-link {
  color: #5c6e66;
  font-weight: 700;
  transition: all 0.2s;
}
.register-link:hover {
  color: #cc805c;
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

/* Social Separator + Icons Inline */
.or-divider {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  width: 100%;
  margin: 12px 0;
}

.or-divider-text {
  color: #a59ab8;
  font-size: 13px;
  font-weight: 700;
  flex-shrink: 0;
}

.line {
  flex-grow: 1;
  height: 2px;
  background: rgba(165, 154, 184, 0.2);
  border-radius: 2px;
}

/* Social Icons (Compact) */
.social-icon-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #ffffff;
  border: 1.5px solid rgba(165, 154, 184, 0.25);
  cursor: pointer;
  box-shadow: 
    3px 3px 6px rgba(100, 90, 130, 0.08),
    inset 1px 1px 2px rgba(255, 255, 255, 0.5);
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  flex-shrink: 0;
}

.social-icon-btn:hover {
  transform: translateY(-1.5px);
  border-color: #9f8ed6;
  box-shadow: 
    0 8px 16px rgba(159, 142, 214, 0.15),
    inset 1px 1px 2px rgba(255, 255, 255, 0.5);
}

.social-icon-btn:active {
  transform: translateY(0.5px);
  box-shadow: inset 2px 2px 4px rgba(0, 0, 0, 0.06);
}

.social-icon-svg {
  width: 16px;
  height: 16px;
}
</style>
