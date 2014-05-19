<?php
/**
 * Created by PhpStorm.
 * User: kristoffer
 * Date: 2014-05-16
 * Time: 11:17
 */

$output = array();

$output[] = array(
		'url' => 'my_alias',
		'global' => false,
		'redirect' => true
);

header('Content-Type: application/json');
echo json_encode($output, JSON_FORCE_OBJECT);