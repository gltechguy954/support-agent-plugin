/* eslint-disable no-unused-vars */
'use strict';

/**
 * Load the request module, to use in the lang task
 */
const request = require('request');

/**
 * File-system module
 */
const fs = require('fs');

/**
 * PurgeCSS
 */
const purgecss = require('@fullhuman/postcss-purgecss')({

  // Specify the paths to all of the template files in your project.
  content: [
    'views/**/*.php',
    'inc/**/*.php',
    // etc.
  ],

  // Include any special characters you're using in this regular expression
  defaultExtractor: (content) => content.match(/[\w-/:]+(?<!:)/g) || [],

});

module.exports = function(grunt) {

  /**
   * Grunt configuration tasks
   */
  grunt.initConfig({

    /**
     * Loads the Package info, we use this to name builds and more.
     */
    pkg: grunt.file.readJSON('package.json'),

    /**
     * Add the list of available translations and where to get them from. We use this to fetch .po files for build.
     */
    lang: grunt.file.readJSON('.lang.json'),

    /**
     * Composer install without dev dependencies.
     */
    exec: {
      php_scoper_prod: {
        command: `
        rm -rf ./dependencies
        && ./../../vendor/bin/php-scoper add-prefix --output-dir=./.dependencies --force
        && composer dump-autoload --working-dir ./.dependencies --classmap-authoritative
        && mv ./.dependencies/vendor ./dependencies
        && rm -rf ./.dependencies
        && rm -rf ./vendor`,
        stdout: true,
        stderr: true,
        cwd: './release/<%= pkg.version %>/',
      },
      composer_install: {
        command: 'composer install --no-dev',
        stdout: true,
        stderr: true,
        cwd: './release/<%= pkg.version %>/',
      },
      remove_composer_files: {
        command() {

          const commands = [
            // Remove really heavy fonts
            "grep -L -E 'DejaVuS|FreeSerif|FreeMono' ./dependencies/mpdf/mpdf/ttfonts/* | xargs -d '\n' rm",
            'ls ./dependencies/fzaninotto/faker/src/Faker/Provider/',
            // eslint-disable-next-line max-len
            'find ./dependencies/fzaninotto/faker/src/Faker/Provider/ -mindepth 1 -maxdepth 1 -type d -not -name en_US -exec rm -rf \'{}\' \\;',
            // 'rm "./dependencies/mpdf/mpdf/ttfonts/Sun-ExtA.ttf"',
            // 'rm "./dependencies/mpdf/mpdf/ttfonts/Sun-ExtB.ttf"',
            // 'rm "./dependencies/mpdf/mpdf/ttfonts/UnBatang_0613.ttf"',
            // 'rm "./dependencies/mpdf/mpdf/ttfonts/Aegyptus.otf"',
            // 'rm "./dependencies/mpdf/mpdf/ttfonts/Jomolhari.ttf"',
            // 'rm "./dependencies/mpdf/mpdf/ttfonts/Aegean.otf"',
            // 'rm "./dependencies/mpdf/mpdf/ttfonts/Akkadian.otf"',
            // 'rm "./dependencies/mpdf/mpdf/ttfonts/Quivira.otf"',
            // 'rm "./dependencies/mpdf/mpdf/ttfonts/FreeSerifBold.ttf"',
            // 'rm "./dependencies/mpdf/mpdf/ttfonts/XB RiyazBdIt.ttf"',
            // 'rm "./dependencies/mpdf/mpdf/ttfonts/XB RiyazBd.ttf"',
            // 'rm "./dependencies/mpdf/mpdf/ttfonts/XB Riyaz.ttf"',
            // 'rm "./dependencies/mpdf/mpdf/ttfonts/XB RiyazIt.ttf"',
            'true',
          ].join(' ; ');

          return 'rm composer.* ; rm scoper.inc.php && ' + commands;

        },
        stdout: false,
        stderr: false,
        cwd: './release/<%= pkg.version %>/',
      },
    },

    /**
     * HTTP Task
     *
     * Calls WordPress and WooCommerce remote servers to retrieve the current stable version.
     * This is used on the rewrite tasks above.
     */
    http: {

      /**
       * Gets the current stable WordPress version.
       */
      wp_version: {
        options: {
          url: 'https://api.wordpress.org/core/version-check/1.7/',
          callback(error, response, body) {

            if (error) {

              return;

            }

            const res = JSON.parse(body);

            const current = res.offers[0].version;

            grunt.config.set('pkg.wp_version', current);

          },
        },
      },

      /**
       * Gets the current stable WooCommerce version.
       */
      wc_version: {
        options: {
          url: 'http://api.wordpress.org/plugins/update-check/1.1/?plugins',
          method: 'POST',
          form: {
            plugins: '{"plugins":{"woocommerce/woocommerce": {}}}',
          },
          callback(error, response, body) {

            if (error) {

              return;

            }

            const res = JSON.parse(body);

            const current = res.plugins['woocommerce/woocommerce'].new_version;

            grunt.config.set('pkg.wc_version', current);

          },
        },
      },
    },

    /**
     * Rewrite Task
     *
     * Rewrite parts of files depending on other events. E.g. version bump, etc.
     */
    rewrite: {

      /**
       * Changes the version number on the main plugin file and other places after a bump on the package.json version number.
       */
      version: {
        src: ['./<%= pkg.name %>.php', './inc/class-<%= pkg.name %>.php'],
        editor(contents) {

          const version = grunt.config.get('pkg.version');

          contents = contents.replace(/Version:(.)*/g, 'Version: ' + version);

          contents = contents.replace(
            /@version  (.)*/g,
            '@version  ' + version
          );

          contents = contents.replace(
            /public \$version = '(.)*/g,
            "public $version = '" + version + "';"
          );

          return contents;

        },
      },

      /**
       * Updates the Tested Up to version of WordPress on the readme.txt file.
       */
      wp_version: {
        src: './readme.txt',
        editor(contents) {

          const wp_version = grunt.config.get('pkg.wp_version');

          contents = contents.replace(
            /Tested up to:(.)*/g,
            'Tested up to: ' + wp_version
          );

          return contents;

        },
      },

      /**
       * Updates the Tested Up to version of WooCommerce on the readme.txt file.
       */
      wc_version: {
        src: './readme.txt',
        editor(contents) {

          const wc_version = grunt.config.get('pkg.wc_version');

          contents = contents.replace(
            /WC tested up to:(.)*/g,
            'WC tested up to: ' + wc_version
          );

          return contents;

        },
      },
    },

    /**
     * Prompt Task
     *
     * We use this to ask the developer for a release message before a new release is created via grunt-bumper
     */
    prompt: {
      target: {
        options: {
          questions: [
            {
              config: 'message', // arbitray name or config for any other grunt task
              type: 'input', // list, checkbox, confirm, input, password
              message:
                'Enter a commit message (an empty message will abort the process)',
            },
          ],
        },
      },
    },

    /**
     * Bumper Task
     *
     * Increases the version number of the package.json and creates a new tag release on Github.
     */
    bumper: {
      options: {
        tasks: [
          'default',
          'rewrite:version',
          'lang',
          'potomo',
        ],
        addFiles: ['.', './.'],
        commitMessage: '<%= message %>', //|| "Release v%VERSION%"
      },
    },

    /**
     * Watch Task
     *
     * Watches the filesystem for changes in specific files, trigerring different tasks when it
     * detects a change.
     */
    watch: {

      /**
       * SASS Watcher
       */
      sass: {
        files: [
          './assets/sass/*.scss',
          './assets/sass/**/*.scss',
          '**/assets/sass/*.scss',
          '**/assets/sass/**/*.scss',
        ],
        tasks: [
          'sass',
          // 'csscomb',
          'cssmin',
        ],
      },

      /**
       * JavaScript Watcher
       */
      js: {
        files: [
          '<%= jshint.all %>',
          './assets/js/*.js',
          './assets/js/wu-*.js',
          '!./assets/js/lib/*.js',
        ],
        tasks: [
          'jshint',
          'terser',
        ],
      },

      /**
       * PHP Watcher
       */
      php: {
        files: [
          './*.php',
          './**/*.php',
          '!./dependencies/**/*.php',
          '!./vendor/**/*.php',
          '!./release/**/*.php',
        ],
        tasks: [],
      },

      /**
       * Tailwind Watcher
       */
      postcss: {
        files: 'ui/src/tailwindcss/**/*.css',
        tasks: ['postcss'],
        options: {
          interrupt: true,
        },
      },
    },

    /**
     * PostCSS - TailwindCSS and Autoprefixer
     * */
    postcss: {
      options: {
        map: false, // inline sourcemaps
        processors: [
          require('tailwindcss')(),
          require('autoprefixer')({ overrideBrowserslist: 'last 2 versions' }), // add vendor prefixes
          // ...process.env.NODE_ENV === 'production' ? [purgecss] : [],
        ],
      },
      dist: {
        expand: true,
        cwd: 'assets/src/',
        src: ['*.css'],
        dest: 'assets/css/',
        ext: '.css',
      },
    },

    /**
     * JS Hint Task
     *
     * Validates JS code and prevents builds when errors are found.
     */
    jshint: {
      options: {
        jshintrc: '.jshintrc',
      },
      all: [
        'Gruntfile.js',
        './assets/js/*.js',
        './assets/js/**/*.js',
        './assets/js/modules/*.js',
        '!./assets/js/plugins/*.js',
        '!./assets/js/*.min.js',
        '!./assets/js/_common.js',
        '!./assets/js/meta-boxes.js',
        '!./assets/js/wu-*.js',
        '!./assets/js/meta-boxes-plan.js',
        '!./assets/js/wu-select2.js',
        '!./assets/js/dependencies/*.js',
        '!./assets/js/lib/*.js',
      ],
    },

    /**
     * SASS Task
     *
     * Compiles the .scss files into CSS files.
     */
    sass: {
      dist: {
        options: {
          sourcemap: 'none',
          // loadPath: require( 'node-bourbon' ).includePaths
        },
        files: [
          {
            expand: true,
            src: [
              '*.scss',
              './inc/**/*.scss',
              './assets/sass/*.scss',
              '!./inc//action-scheduler/**/*.scss',
            ],
            rename(dest, src) {

              return src.replace('sass', 'css'); // The `src` is being renamed; the `dest` remains the same

            },
            ext: '.css',
          },
        ],
      },
    },

    /**
     * CSSMIN taks
     *
     * Minifies the CSS files after they are generated by the SASS task.
     */
    cssmin: {
      options: {
        banner:
          '/*! <%= pkg.fullName %> - v<%= pkg.version %>\n' +
          ' * <%= pkg.homepage %>\n' +
          ' * <%= grunt.template.today("yyyy") %>;' +
          ' */\n',
      },
      minify: {
        expand: true,
        src: [
          '*.css',
          './inc/**/*.css',
          './assets/**/*.css',
          '!*.min.css',
          '!./inc/**/*.min.css',
          '!./assets/src/*.css',
          '!./assets/**/*.min.css',
        ],
        rename(dest, src) {

          return src; // The `src` is being renamed; the `dest` remains the same

        },
        ext: '.min.css',
      },
    },

    /**
     * CSS Comb Task
     *
     * Re-organize CSS directivies based on a set of rules defined on the .csscomb.json file.
     */
    csscomb: {
      dynamic_mappings: {
        expand: true,
        src: [
          '*.css',
          './inc/**/*.css',
          './assets/**/*.css',
          '!*.min.css',
          '!./inc/**/*.min.css',
          '!./assets/**/*.min.css',
        ],
        ext: '.css',
        options: {
          config: './.csscomb.json',
        },
      },
    },

    /**
     * Imagemin Task
     *
     * Optimizes all images inside the assets/img folder
     */
    imagemin: {
      // Task
      dynamic: {
        // Another target
        files: [
          {
            expand: true, // Enable dynamic expansion
            cwd: './assets/img/', // Src matches are relative to this path
            src: ['*.{png,jpg,gif}'], // Actual patterns to match
            dest: './assets/img/', // Destination path prefix
          },
        ],
      },
    },

    /**
     * JS Uglify Task
     *
     * Concatenates the JavaScript, generating .min files.
     */
    terser: {
      // dist: {
      //   files: {
      //     './assets/js/wu-main.js': [
      //       './assets/js/dependencies/*.js',
      //       './assets/js/_*.js',
      //       './assets/js/_main.js',
      //     ],
      //   },
      //   options: {
      //     mangle: false,
      //     compress: false,
      //     // JS source map: to enable, uncomment the lines below and update sourceMappingURL based on your install
      //     // sourceMap: "./assets/js/scripts.min.js.map",
      //     // sourceMappingURL: "./assets/js/scripts.min.js.map"
      //   },
      // },
      minify_others: {
        files: [
          {
            expand: true,
            src: [
              './assets/js/*.js',
              './inc/**/js/*.js',
              './assets/js/lib/*.js',
              './assets/js/gateways/*.js',
              '!./assets/js/_*.js',
              '!./assets/js/*.min.js',
              '!./assets/js/lib/*.min.js',
              './assets/js/plugins/*.js',
              '!./assets/js/plugins/_*.js',
              '!./assets/js/plugins/*.min.js',
              '!./assets/js/**/*.min.js',
              '!./inc/action-scheduler/js/*.js',
              '!./inc/**/js/*.min.js',
            ],
            dest: './',
            rename(dst, src) {

              return dst + '/' + src.replace('.js', '.min.js');

            },
          },
        ],
      },
    },

    pot: {
      options: {
        encoding: 'UTF-8',
        msgmerge: true,
        text_domain: '<%= pkg.name %>', //Your text domain. Produces my-text-domain.pot
        dest: './lang/', //directory to place the pot file
        keywords: [
          //WordPress localisation functions
          '__:1',
          '_e:1',
          '_x:1,2c',
          'esc_html__:1',
          'esc_html_e:1',
          'esc_html_x:1,2c',
          'esc_attr__:1',
          'esc_attr_e:1',
          'esc_attr_x:1,2c',
          '_ex:1,2c',
          '_n:1,2',
          '_nx:1,2,4c',
          '_n_noop:1,2',
          '_nx_noop:1,2,3c',
        ],
      },
      files: {
        src: [
          './*.php',
          './**/*.php',
          '!./release/**/*.php',
          '!./paradox/**/*.php',
          '!./vendor/**',
          '!./vendor-bin/**',
          '!./build/**',
          '!./tests/**',
          '!./node_modules/**',

          '!./inc/duplicate/**/*.php',
          '!./inc/setup/importer/**/*.php',

          '!./grunt/**/*.php',
          '!./inc/tgm/*.php',
        ],
        expand: true,
      },
    },

    /**
     * PO2MO Task
     *
     * Compiles all .po files inside the /lang folder into .mo files, necessary for gettext to work.
     */
    potomo: {
      dist: {
        options: {
          poDel: false,
        },
        files: [
          {
            expand: true,
            cwd: './lang',
            src: ['*.po'],
            dest: './lang',
            ext: '.mo',
            nonull: true,
          },
        ],
      },
    },

    /**
     * Copy Task
     *
     * Copies the build files into a release sub-folder with the version number.
     * This folder will serve as a basis for the compress task, which generates the .zip file.
     */
    copy: {
      main: {
        cwd: './',
        expand: true,
        src: [
          '**',
          '!release/**',
          '!vendor/**',
          '!dependencies/**',
          '!node_modules/**',
          '!tests/**',
          '!.*',
          '!*.xml',
          '!views/*.xml',
          '!*.json',
          '!*.config.js',
          '!composer.*',
          '!README.md',
        ],
        dest: './release/<%= pkg.version %>/',
      },
      composer: {
        cwd: './',
        src: 'composer.json',
        dest: './release/<%= pkg.version %>/composer.json',
        options: {
          process(content) {

            content = content.replace(/bash .\/.build.dev.sh/g, 'echo \\"Skipping PHP Scoper...\\"');

            const contentJSON = JSON.parse(content);

            contentJSON['require-dev'] = {};

            return JSON.stringify(contentJSON, 2);

          },
        },
      },
    },

    /**
     * Compress Task
     *
     * Generates the final .zip file with the current version, that can be shared
     * with users and installed via the WP panel.
     */
    compress: {
      main: {
        options: {
          mode: 'zip',
          archive: '././release/<%= pkg.name %>.<%= pkg.version %>.zip',
        },
        expand: true,
        cwd: './release/<%= pkg.version %>/',
        src: ['**/*'],
        dest: '<%= pkg.name %>/',
      },
    },

    /**
     * TODO Task
     *
     * This scans the code base for ocurrences of apply_filters and do_action calls.
     * We use this to generate a actions.md file containing all filters and hooks WP Ultimo Support Agents has available.
     */
    todo: {
      actions: {
        options: {
          marks: [
            {
              name: 'apply_filters',
              pattern: /(apply_filters)/,
              color: 'yellow',
            },
            {
              name: 'do_action',
              pattern: /(do_action)/,
              color: 'orange',
            },
          ],
          file: './actions.md',
          title: 'WP Ultimo Support Agents - Actions and Filters',
          githubBoxes: false,
          colophon: true,
          usePackage: true,
        },
        src: [
          './*.php',
          './**/*.php',
          '!./release/**/*.php',
          '!./paradox/**/*.php',
          '!./grunt/**/*.php',
          '!./inc/tgm/*.php',
        ],
      },
      todos: {
        options: {
          marks: [
            {
              name: 'FIX',
              pattern: /FIXME/,
              color: 'red',
            },
            {
              name: 'TODO',
              pattern: /TODO/,
              color: 'yellow',
            },
            {
              name: 'NOTE',
              pattern: /NOTE/,
              color: 'blue',
            },
            {
              name: 'TODO',
              pattern: /@todo/,
              color: 'yellow',
            },
          ],
          file: './todos.md',
          title: 'WP Ultimo Support Agents - Todos',
          githubBoxes: true,
          colophon: true,
          usePackage: true,
        },
        src: [
          './*.php',
          './**/*.php',
          '!./release/**/*.php',
          '!./paradox/**/*.php',
          '!./grunt/**/*.php',
          '!./vendor/**/*.php',
          '!./vendor-bin/**/*.php',
          '!./dependencies/**/*.php',
          '!./inc/tgm/*.php',
        ],
      },
    },

  });

  /**
   * Lang Task
   *
   * This goes to our translation platform and fetches the more recent versions
   * of the .po files before we finish the build.
   */
  grunt.registerMultiTask('lang', 'Get the latest .po files', function() {

    const done = this.async();

    const lang = this.target;

    const url = this.data;

    if (! url || ! lang) {

      done();

    } // end if;

    // eslint-disable-next-line no-console
    console.log('Copying: ' + this.data);

    const r = request(url);

    r.on('response', function(res) {

      // eslint-disable-next-line no-console
      console.info('Got file');

      res.pipe(fs.createWriteStream('./lang/' + grunt.config.get('pkg.name') + '-' + lang + '.po'));

      setTimeout(function() {

        done();

      }, 3000);

    });

  });

  /**
   * Default Task
   *
   * Running grunt on the CLI will run these.
   */
  grunt.registerTask('default', [
    'sass',
    'csscomb',
    'cssmin',
    'terser',
    'imagemin',
    'http',
    'rewrite:version',
    'rewrite:wp_version',
    'rewrite:wc_version',
    // 'todo',
    // 'pot',
  ]);

  /**
   * Build Task
   *
   * Generates a new build.
   */
  grunt.registerTask('build', [
    'postcss',
    'default',
    // 'lang',
    // 'potomo',
    'copy',
    'exec:composer_install',
    'exec:php_scoper_prod',
    'exec:remove_composer_files',
    'compress',
  ]);

  /**
   * Clean Build Task
   *
   * Only packages the already compiled files into the zip.
   */
  grunt.registerTask('clean-build', [
    'copy',
    'compress',
  ]);

  /**
   * Tag Task
   *
   * Generates a new tag release and tags it on githug. Alias of the commit task.
   */
  grunt.registerTask('tag', [
    'commit',
  ]);

  /**
   * Commit Task
   *
   * Creates a new Tag on Github and builds a new release, after bumping the version number.
   */
  grunt.task.registerTask('commit', 'Commit your stuff', function(type) {

    /**
     * List of tasks to execute.
     */
    const tasks = ['prompt'];

    let bumper = 'bumper';

    if (arguments.length === 1) {

      bumper = 'bumper:' + type;

    }

    /**
     * Add bumper taks
     */
    tasks.push(bumper, 'copy', 'compress');

    /**
     * Run the tasks
     */
    grunt.task.run(tasks);

  });

  /**
   * We need to load external Grunt tasks so grunt can use them.
   */
  grunt.loadNpmTasks('grunt-csscomb');

  grunt.loadNpmTasks('grunt-contrib-jshint');

  grunt.loadNpmTasks('grunt-terser');

  grunt.loadNpmTasks('grunt-contrib-cssmin');

  grunt.loadNpmTasks('grunt-contrib-watch');

  grunt.loadNpmTasks('grunt-sass');

  grunt.loadNpmTasks('grunt-contrib-copy');

  grunt.loadNpmTasks('grunt-contrib-compress');

  grunt.loadNpmTasks('grunt-bumper');

  grunt.loadNpmTasks('grunt-contrib-imagemin');

  grunt.loadNpmTasks('grunt-prompt');

  grunt.loadNpmTasks('grunt-rewrite');

  grunt.loadNpmTasks('grunt-todo');

  grunt.loadNpmTasks('grunt-potomo');

  grunt.loadNpmTasks('grunt-pot');

  grunt.loadNpmTasks('grunt-http');

  grunt.loadNpmTasks('grunt-exec');

  grunt.loadNpmTasks('grunt-postcss');

};
