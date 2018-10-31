<?php

$context = stream_context_create(
[  
    'ssl'=>
    [
        'verify_peer' => true,
        'cafile' => '../config/CA_Root_Certificates/ca-bundle.crt'
    ]
]);

libxml_set_streams_context($context);