module.exports = function(grunt) {



    var jsFiles = ['assets/components/jquery/dist/jquery.min.js','assets/components/bootstrap/dist/js/bootstrap.min.js','assets/js/script.js'];

    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        less: {
            development: {
                options: {
                    paths: ["assets"]
                },
                files: {
                    "assets/built/main.css": "assets/less/index.less"
                }
            }
        },
        cssmin: {
            options: {
                shorthandCompacting: false,
                roundingPrecision: -1,
                noRebase: false, // whether to skip URLs rebasing
                root: 'www'
            },
            target: {
                files: {
                    'assets/built/main.min.css': ['assets/built/main.css']
                }
            }
        },
        uglify: {
            my_target: {
                files: {
                    'assets/built/main.min.js': jsFiles
                }
            }
        },
        watch: {
            scripts: {
                files: ['assets/js/*','assets/css/*','assets/less/*'],
                tasks: ['less'/*,'cssmin','uglify'*/]
            }
        }
    });

    // Load the plugin that provides the "uglify" task.
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');

    // Default task(s).
    grunt.registerTask('default', ['less','uglify',/*'cssmin',*/'watch']);
};