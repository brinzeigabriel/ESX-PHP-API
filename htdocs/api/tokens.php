<?php

$payload = [
    "sub" => $user["id"],
    "name" => $user["name"],
    "exp" => time() + 20 //expire token after 20 seconds
]; 

$access_token = $codec->encode($payload);

$refresh_token_expiry = time() + 432000; // refresh token expire after 5 days
$refresh_token = $codec->encode([
    "sub" => $user["id"],
    "exp" => $refresh_token_expiry
]);

echo json_encode([
    "access_token" => $access_token,
    "refresh_token" => $refresh_token
]); //contine id si nume codad