module.exports = function(grunt) {
  require('phplint').gruntPlugin(grunt);
  grunt.initConfig({
    phplint: {
      options: {
        stdout: true,
        stderr: true
      },
      files: ['src/API/**/*.php', 'examples/**/*.php']
    },
    phpcs: {
      source: {
        src: ['src/API/**/*.php']
      },
      examples: {
        src: ['examples/**/*.php']
      },
      options: {
        bin: 'vendor/bin/phpcs',

        //standard: 'PSR2',
        colors: true,
        standard: 'phpcs.xml'
      }
    }
  });

  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-phpcs');

  grunt.registerTask('default', ['phplint', 'phpcs']);
};
