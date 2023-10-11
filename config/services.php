<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // 'firebase' => [
    //     "apiKey"=> "AIzaSyBwNV9Lso2Z0Wm6zg_H1VnYCNtZNsXvkng",
    //     "authDomain"=> "go4sumbergedang-78702.firebaseapp.com",
    //     "databaseURL"=> "https://go4sumbergedang-78702-default-rtdb.firebaseio.com",
    //     "projectId"=> "go4sumbergedang-78702",
    //     "storageBucket"=> "go4sumbergedang-78702.appspot.com",
    //     "messagingSenderId"=> "299884546534",
    //     "appId"=> "1:299884546534:web:166d98359e3a46c0126e27",
    //     "measurementId"=> "G-0VNV48G110"
    // ],

];
