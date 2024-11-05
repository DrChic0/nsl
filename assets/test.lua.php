<?php
$webhookurl = "https://discord.com/api/webhooks/1108911549516611775/KIxAOFoMyDBO6GRvxFXKb-acSQ4QxbHWZz8Giq3h9nPFMyuWbBWgceDQ8N3jjOxsDuXd";
        $timestamp = date("c", strtotime("now"));

        $json_data = json_encode([
            "content" => "i got called",
            "username" => "novetusserverlist",
            "avatar_url" => "https://www.novetusserverlist.com/img/66134779.png",
            "tts" => false,
            "embeds" => [],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

        $ch = curl_init( $webhookurl );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt( $ch, CURLOPT_POST, 1);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt( $ch, CURLOPT_HEADER, 0);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec( $ch );
        curl_close( $ch );
?>
print("Hello World!")