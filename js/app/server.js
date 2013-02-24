define([
	'backbone',
], function(Backbone) {
	
	var Server = Backbone.Model.extend({
		url: 'server/',
		initialize: function() {
			this.cid = this.id;
		}
	});

	return Server;

});
