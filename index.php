<?php
function logP($message) { echo $message.PHP_EOL; }

include 'vendor/autoload.php';
include 'libs/Server.php';
include 'libs/MySQLServer.php';
include 'libs/PostgresServer.php';
include 'libs/Cluster.php';


$servers = array(
	new MySQLServer('127.0.0.1', '22585', 'msandbox', 'msandbox'),
	new MySQLServer('127.0.0.1', '22586', 'msandbox', 'msandbox'),
	new MySQLServer('127.0.0.1', '22587', 'msandbox', 'msandbox'),
);

$cluster = new Cluster($servers);


$app = new Slim\Slim();

$app->get('/', function() use($app, $cluster) {
	$app->render('index.php', array('cluster'=>$cluster));
});

$app->get('/readonly/:serverid/:state', function($serverId, $state) use($app, $cluster) {
	$server = $cluster->getServer($serverId);
	$server->command('SET @@global.read_only = '.(int)$state);
});

$app->get('/replication/:serverid/:state', function($serverId, $state) use($app, $cluster) {
	$status = (int)$state;
	$server = $cluster->getServer($serverId);
	if(!$status) {
		echo $server->startReplication();
	} else {
		echo $server->pauseReplication();
	}
});

$app->get('/cluster/', function() use($app, $cluster) {
	echo json_encode($cluster->jsonSerialize(), JSON_HEX_QUOT);
});

$app->put('/server/', function() use($app, $cluster) {
	$data = json_decode($app->request()->getBody());
	$server = $cluster->getServer($data->id);
	if($data->isReplicating) {
		$server->startReplication();
	} else {
		$server->pauseReplication();
	}
	echo json_encode($server->jsonSerialize());
});

$app->run();
