<?php
//Firbase Integration
require get_theme_file_path('/vendor/autoload.php');

//echo get_theme_file_path('/vendor/autoload.php');die;

echo get_theme_file_path('/config/ebook-6bc3d-firebase-adminsdk-id4oa-6d8bdc81c7.json');die;


use Kreait\Firebase\Factory;

$factory = (new Factory)
  ->withServiceAccount(get_theme_file_path('/config/ebook-6bc3d-firebase-adminsdk-id4oa-6d8bdc81c7.json'))
  ->withDatabaseUri('https://ebook-6bc3d-default-rtdb.firebaseio.com/');

  $database = $factory->createDatabase();