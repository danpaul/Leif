<?php

	include "./leif.php";

//////////////////////
//BASIC USAGE
//////////////////////

	$leif = new Leif;

	$leif->create('foo', 'bar');

	print($leif->read('foo')); //~> 'bar'

	$leif->update('foo', 'bat');

	print($leif->read('foo')); //~> 'bat'

	$leif->upsert('foo', 'biz'); //updates if key exists, otherwise creates

	print($leif->read('foo')); //~> 'biz'

	$leif->delete('foo');

	if(!$leif->key_exists('foo')){print("nothing to see here!");} //~> 'nothing to...'

//////////////////////
//SERIALIZATION
//////////////////////

	$my_array = array(1,2,3);

	$leif->create('my_array', $my_array);

	$new_array = $leif->read('my_array');

	var_dump($new_array[0]); //~> array(3){[0]=? 1...

//////////////////////
//JSON
//////////////////////

	$leif->upsert('json_array', $my_array, 'json');

	var_dump($leif->read('json_array', 'json')); //~> array(3){[0]=? 1...

	var_dump($leif->read('json_array', 'json-raw')); //~> string(7) "[1,2,3]"

?>
