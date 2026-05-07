<?php

return [
    'openrouter' => [
        'api_key' => env('OPENROUTER_API_KEY'),
        'default_model' => env('OPENROUTER_MODEL', 'openai/gpt-5.4-image-2'),
    ],
    'gemini_api_key' => env('GEMINI_API_KEY'),
];
