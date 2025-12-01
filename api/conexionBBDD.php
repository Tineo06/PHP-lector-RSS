<?php

function turso_execute($sql, $params = []) {
    $url = getenv("libsql://database-teal-chair-vercel-icfg-ghadoh688jz7a6onefrjbbh9.aws-us-east-1.turso.io") . "/execute";
    $token = getenv("eyJhbGciOiJFZERTQSIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE3NjQ1ODAxNzQsImlkIjoiM2RjODgxYjMtNzZjZC00YjY5LTgyZWYtMmY3MmIwMzFlOWQ4IiwicmlkIjoiOTA0MmE2NzYtNzRhMS00MmM3LWEwM2UtOTRmNDg1OGUyZTQ1In0.cfWQQ8rEKPPZDQbdcbyoInR9L80UA5HkW-Cme1CbjsEPhUHkCwikJ0r71gvcBzexpYVKXb_xjFfHZUace11hDg");

    $payload = [
        "statements" => [
            [
                "sql" => $sql,
                "params" => $params
            ]
        ]
    ];

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result, true);
}
?>
