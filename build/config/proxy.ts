import type { ProxyOptions } from 'vite'
import { getProxyConfig } from '../../settings/proxy-config'

export function createViteProxy(isUseProxy = true, proxyType: ProxyType) {
  if (!isUseProxy) return undefined

  const proxyConfigArray = getProxyConfig(proxyType)
  const proxy: Record<string, string | ProxyOptions> = {}
  for (const proxyConfig of proxyConfigArray) {
    proxy[proxyConfig.prefix] = {
      target: proxyConfig.target,
      changeOrigin: true,
      bypass(req, res, options) {
        const proxyUrl =
          new URL(
            (options.rewrite ? options.rewrite(req.url as string) : req.url) ||
              '',
            options.target as string
          )?.href || ''
        res.setHeader('x-res-proxyUrl', proxyUrl)
      },
      rewrite: (path: string) =>
        path.replace(new RegExp(`^${proxyConfig.prefix}`), '')
    }
  }
  return proxy
}
