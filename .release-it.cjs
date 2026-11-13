// Configuración de release-it: bump de package.json, CHANGELOG.md,
// commit "chore(release): X.Y.Z" y tag vX.Y.Z (sin push ni publicación).
const { version } = require('./package.json');

const repoUrl = 'https://github.com/xaamin/fractal';

module.exports = {
  git: {
    commitMessage: 'chore(release): ${version}',
    tagName: 'v${version}',
    tagAnnotation: 'chore(release): ${version}',
    push: false,
  },
  npm: {
    publish: false,
  },
  plugins: {
    '@release-it/conventional-changelog': {
      infile: 'CHANGELOG.md',
      header: '# Changelog',
      preset: {
        name: 'conventionalcommits',
        // Como standard-version: antes de 1.0.0 un BREAKING CHANGE sube minor, no major.
        preMajor: version.startsWith('0.'),
        types: [
          { type: 'feat', section: 'Features', effect: 'bump' },
          { type: 'fix', section: 'Bug Fixes', effect: 'bump' },
          { type: 'chore', effect: 'hidden' },
          { type: 'docs', effect: 'hidden' },
          { type: 'style', effect: 'hidden' },
          { type: 'refactor', effect: 'hidden' },
          { type: 'perf', effect: 'hidden' },
          { type: 'test', effect: 'hidden' },
        ],
      },
      context: {
        repoUrl,
      },
    },
  },
};
