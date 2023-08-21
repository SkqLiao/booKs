const proxyConfigMappings: Record<ProxyType, ProxyConfig> = {
  dev: {
    prefix: '/api',
    target: 'http://localhost'
  },
  test: {
    prefix: '/api',
    target: 'http://localhost'
  },
  prod: {
    prefix: '/api',
    target: 'http://localhost'
  }
}

export function getProxyConfig(envType: ProxyType = 'dev'): ProxyConfig {
  return proxyConfigMappings[envType]
}
