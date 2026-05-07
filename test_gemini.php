<?php
$key = config('ai.gemini_api_key');
$models = ['gemini-2.5-flash', 'gemini-2.5-flash-lite-preview-06-17', 'gemini-2.5-flash-lite'];
foreach ($models as $m) {
    $res = Illuminate\Support\Facades\Http::timeout(10)->post(
        'https://generativelanguage.googleapis.com/v1beta/models/' . $m . ':generateContent?key=' . $key,
        ['contents' => [['parts' => [['text' => 'Say OK']]]]]
    );
    $json = $res->json();
    if (isset($json['error'])) {
        echo "$m: ERROR - " . $json['error']['message'] . "\n";
    } else {
        echo "$m: OK - " . data_get($json, 'candidates.0.content.parts.0.text') . "\n";
    }
}
