<?php
header('Content-Type: application/json');
$ch = curl_init("https://api.ipify.org?format=json");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
echo $result;
