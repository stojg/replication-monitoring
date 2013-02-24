// Filename: views/project/list
define([
	'backbone',
	'app/serverview'
], function(Backbone, ServerView) {

	var ClusterView = Backbone.View.extend({

		initialize: function() {
			this.listenTo(cluster, 'reset', this.addAll);
			this.listenTo(cluster, 'all', this.render);
		},

		render: function() {
			var that = this;
			$(this.el).empty();
			_(this._serverViews).each(function(sv) {
				$(that.el).append(sv.render().el);
			});
			return this;
		},

		addAll: function() {
			var that = this;
			this._serverViews = [];
			cluster.each(function(server) {
				that._serverViews.push(new ServerView({
					model : server
				}));
			});
		}
	});

	return ClusterView;

});