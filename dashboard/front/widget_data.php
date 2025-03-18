<?php
include ("../../../inc/includes.php");
include ("../../../inc/config.php");

global $DB;

Session::checkLoginUser();

if(!isset($_POST['query'])) {
    die(json_encode(['error' => 'Query não fornecida']));
}

$query = $_POST['query'];

// Executar query e retornar resultados
$result = $DB->query($query);
$data = array();

while($row = $DB->fetchAssoc($result)) {
    $data[] = $row;
}

echo json_encode($data); 