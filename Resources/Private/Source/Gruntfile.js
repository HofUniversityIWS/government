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
                    "../../Public/Css/main.css": "assets/less/index.less"
                }
            }
        },
        uglify: {
            my_target: {
                files: {
                    '../../Public/JavaScript/main.min.js': jsFiles
                }
            }
        },
        watch: {
            scripts: {
                files: ['assets/js/*','assets/css/*','assets/less/*'],
                tasks: ['less']
            }
        }
    });

    // Load the plugin that provides the "uglify" task.
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');

    // Default task(s).
    grunt.registerTask('default', ['less','uglify','watch']);
};