define([
	'backbone'
], function(Backbone) {

	
	var ServerView = Backbone.View.extend({

		events : {
			'change .toggle' : 'clicked'
		},

		className: "span5",

		initialize: function(){
			this.listenTo(this.model, 'change', this.render);
			this.template = _.template($('#server_template').html());
		},

		render: function() {
			var dict = this.model.toJSON();
			var html = this.template(dict)
			this.$el.html(html);
			this.delegateEvents();
			return this;
		},

		analyzeCellClick : function() {
			// pass the relevant CellModel to the BoardView
			this.parent.clicked(this.model);
		},

		clicked: function(e) {
			this.model.set('isReplicating', $(e.target).is(':checked'));
			this.model.save();
		}

	});
	return ServerView;

});