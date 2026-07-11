import { defineAsyncComponent } from 'vue'

const pageModules = import.meta.glob('../../../../Modules/*/resources/js/pages/**/*.vue')

export function resolveModulePage(alias, pagePath) {
  const cleanPath = pagePath.replace(/^\//, '')
  const normalizedTarget = `${alias}/${cleanPath}`.toLowerCase()

  for (const key of Object.keys(pageModules)) {
    const relativeKey = key
      .replace(/^.*\/Modules\//, '')
      .replace(/\/resources\/js\/pages\//, '/')
      .replace(/\.vue$/, '')
      .toLowerCase()
    if (relativeKey === `${normalizedTarget}/index` || relativeKey === normalizedTarget) {
      return defineAsyncComponent(pageModules[key])
    }
  }

  return null
}
