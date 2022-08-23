<?php
require __DIR__ . '/vendor/autoload.php';

use Ospina\EasySQL\EasySQL;

$easySQL = new EasySQL('encuesta_graduados', 'local', '/../');
//Query
$result = $easySQL->table('form_answers')->select(['*'])->where('is_graduated', '=', 0)->get();
/*$result = $easySQL->table('form_answers')
    ->where('identification_number', '=', '1032425533')
    ->update(['is_graduated' => 1]);*/

/*$result = $easySQL->table('form_answers')
    ->insert([
        'email' => 'prueba@gmail.com',
        'identification_number' => '12345353',
        'name' => 'prueba',
        'last_name' => 'ospina',
        'mobile_phone' => 'testmobile',
        'alternative_mobile_phone' => '',
        'address' => 'skljas',
        'country' => 'colombia',
        'city' => 'ciudad',
        'is_graduated' => 1,
        'answers' => '{"data":"null"}',
        'created_at' => date('Y-m-d H:i:s'),
    ]);*/

$easySQL->dd();

