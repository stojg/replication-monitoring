// Require.js allows us to configure shortcut alias
// There usage will become more apparent further along in the tutorial.
require.config({
	paths: {
		jQuery: 'libs/jquery/jquery',
		underscore: 'libs/underscore/underscore',
		backbone: 'libs/backbone/backbone'
	},

	shim: {
        'underscore': {
            exports: '_'
        },

        'jQuery': {
            exports: '$'
        },

        'backbone': {
            deps: ['underscore', 'jQuery'],
            exports: 'Backbone'
        }
	}
});

require(['app'], function(App){
	// The "app" dependency is passed in as "App"
	App.initialize();
});