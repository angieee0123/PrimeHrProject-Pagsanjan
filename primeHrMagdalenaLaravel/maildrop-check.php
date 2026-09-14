<?php

/**
 * E: reset wording + code, but an ordinary public link (no localhost)
 * F: innocuous wording, but the localhost link
 * Whichever is dropped names the trigger. Deleted after the run.
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;

function inbox(string $mailbox): array
{
    $ctx = stream_context_create(['http' => [
        'method'        => 'POST',
        'header'        => "Content-Type: application/json\r\nAccept: application/json\r\n",
        'content'       => json_encode(['query' => '{ inbox(mailbox: "' . $mailbox . '") { subject date } }']),
        'timeout'       => 20,
        'ignore_errors' => true,
    ]]);

    $body = @file_get_contents('https://api.maildrop.cc/graphql', false, $ctx);

    return $body === false ? [] : (json_decode($body, true)['data']['inbox'] ?? []);
}

$tag   = substr(bin2hex(random_bytes(4)), 0, 6);
$words = 'phrm-e-' . $tag;
$link  = 'phrm-f-' . $tag;

Mail::raw(
    "Your Laravel password reset code\n\n123456\n\nThe code expires in 10 minutes. "
    . "Someone asked to reset the password for this address.\n\n"
    . "Open the reset page: https://example.com/password/forgot\n",
    function ($m) use ($words) {
        $m->to($words . '@maildrop.cc')->subject('Your Laravel password reset code');
    }
);
echo 'sent E (reset wording + code, public link)' . PHP_EOL;

Mail::raw(
    "Hello, this is an ordinary message about nothing in particular.\n\n"
    . "Have a look here: http://localhost/password/forgot\n",
    function ($m) use ($link) {
        $m->to($link . '@maildrop.cc')->subject('Ordinary message');
    }
);
echo 'sent F (innocuous wording + localhost link)' . PHP_EOL . PHP_EOL;

for ($i = 1; $i <= 8; $i++) {
    sleep(15);

    $e = inbox($words);
    $f = inbox($link);

    echo str_pad($i * 15 . 's', 6)
        . ' E_resetwords=' . count($e)
        . ' F_localhostlink=' . count($f) . PHP_EOL;

    if ($e && $f) {
        break;
    }
}
