const path = require('path');

/** @type {import('next').NextConfig} */
const nextConfig = {
  webpack: (config) => {
    config.resolve.alias = {
      ...(config.resolve.alias || {}),
      '@components': path.resolve(__dirname, 'components'),
    };
    return config;
  },
};

module.exports = nextConfig;
