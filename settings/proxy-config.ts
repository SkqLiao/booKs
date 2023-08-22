const proxyConfigMappings: Record<ProxyType, ProxyConfig[]> = {
  dev: [
    {
      prefix: '/api',
      target: 'http://localhost:8080'
    },
    {
      prefix: '/bookapi',
      target: 'http://localhost'
    },
    {
      prefix: '/doubanapi',
      target: 'http://localhost:3000'
    }
  ],
  test: [
    {
      prefix: '/api',
      target: 'http://localhost'
    }
  ],
  prod: [
    {
      prefix: '/api',
      target: 'http://localhost'
    }
  ]
}

export function getProxyConfig(envType: ProxyType = 'dev'): ProxyConfig[] {
  return proxyConfigMappings[envType]
}
