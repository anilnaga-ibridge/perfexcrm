export const SocialAuth = {
  login(provider) {
    window.location.href = `${window.config.path}/api/auth/social/${provider}/redirect`;
  }
};
