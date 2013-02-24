// Filename: app.js
define([
	'jQuery',
	'app/cluster',
	'app/clusterview'
], function($, Cluster, ClusterView){

	var initialize = function() {
		$(document).ajaxError(function() {
			alert("There was an error.");
		});

		// Initialize Backbone views.
		cluster = new Cluster;
		clusterView = new ClusterView({
			el: $("#cluster_container")
		});
		//App.cluster.fetch();
		cluster.stream({interval: 5000});
	};

	return {
		initialize: initialize
	};
});