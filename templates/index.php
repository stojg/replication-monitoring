<!DOCTYPE html>
<html>
	<head>
		<title>Replication and Status</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
		<?php if($refresh): ?>
			<meta http-equiv="refresh" content="<?php echo $refresh; ?>">
		<?php endif; ?>
		<script data-main="js/main" src="js/libs/require/require.js"></script>
	</head>
	<body>

		<script type="text/template" id="server_template">
				
			<h3><%= hostAndPort %></h3>
				
			<% if(!isConnected) { %>
				<div class="alert alert-block alert-error" >
					<p><strong>Error: </strong> Can't connect to server</p>
				</div>
			<% } else { %>
				<form>
					<label class="checkbox" for="replication-<%= id %>">Replication
						<% if(canReplicate) { %>
						<input class="toggle" type="checkbox" <% if (isReplicating) { %>checked="checked"<% } %>name="replication-<%= id %>" id="replication-<%= id %>">
						<% } else { %>
						N/A
						<% } %>
					</label>
				</form>

				<h4>Status</h4>
				<table class="table table-condensed">
					<tr>
						<td>Server id</td>
						<td><%= serverID %></td>
					</tr>
					<tr>
						<td>Hot standby (read only)</td>
						<td><%= hotStandByMode %></td>
					</tr>
					<tr>
						<td>Current log position</td>
						<td><%= currentLogPos %></td>
					</tr>
					<tr>
						<td>Replicating</td>
						<td><%= (isReplicating)?'Yes':'No' %></td>

					</tr>
				</table>

				<% if(_.size(connectedSlaves)) { %>
				<h4>Connected slaves</h4>
				<table class="table table-striped table-bordered">
					<% _.each(connectedSlaves, function(value, idx) { %>
					<tr>
						<td><%= idx %></td>
						<%_.each(value, function(col) { %>
						<td><%= col %></td>
						<% }); %>
					</tr>
					<% }); %>
				</table>
				<% } %>

				<% if(slaveStatus) { %>
				<h4>Slave status</h4>
				<table class="table table-striped table-bordered">
					<% _.each(slaveStatus, function(value, idx) { %>
					<tr>
						<td><%= idx %></td>
						<td><%= value %></td>
					</tr>
					<% }); %>
				</table>
				<% } %>
			<% } %>
		</script>

		<div>
			<h1>Replication and Status</h1>
		</div>
		<div id="cluster_container"></div>
	</body>
</html>