<?php

require 'vendor/autoload.php';
require 'vendor/illuminate/support/helpers.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

///// Illuminate/Database /////
use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => getenv('DB_HOST'),
    'database'  => getenv('DB_NAME'),
    'username'  => getenv('DB_USER'),
    'password'  => getenv('DB_PASS'),
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

// Set the event dispatcher used by Eloquent models... (optional)
//use Illuminate\Events\Dispatcher;
//use Illuminate\Container\Container;
//$capsule->setEventDispatcher(new Dispatcher(new Container));

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();

// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();
///// /////

function toJson($data){
	header('Content-type: application/json');
	echo json_encode($data, JSON_PRETTY_PRINT);
	exit;
}

$app = new \Slim\Slim();

$app->get('/pokemon/:id', function ($id) {
    if($id!=0){			
		$Pokemon = Capsule::table('pokemon')->where('id',$id)->first();
		if($Pokemon){
			$Pokemon->name = $Pokemon->identifier;
			$Abilities = Capsule::table('pokemon_abilities as pa')
						   ->leftJoin('abilities as a','pa.ability_id','=','a.id')
						   ->select('a.id','pa.slot','a.identifier as name')
						   ->where('pa.pokemon_id',$id)
						   ->get();
			$Pokemon->abilities = $Abilities;

			$Types = Capsule::table('pokemon_types as pt')
					   ->leftJoin('types as t','pt.type_id','=','t.id')
					   ->select('t.id','pt.slot','t.identifier as name')
					   ->where('pt.pokemon_id',$id)
					   ->get();
			$Pokemon->types = $Types;
		}
		return toJson($Pokemon);
	}
});

$app->get('/pokemon/:id/types', function ($id) {
    $Pokemon = Capsule::table('pokemon')->where('id',$id)->first();
	if($Pokemon){
		$Types = Capsule::table('pokemon_types as pt')
				   ->leftJoin('types as t','pt.type_id','=','t.id')
				   ->select('t.id','pt.slot','t.identifier as name')
				   ->where('pt.pokemon_id',$id)
				   ->get();
		$Pokemon->types = $Types;
	}
	return toJson($Pokemon);
});

$app->run();