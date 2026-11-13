<?php

declare(strict_types=1);

require __DIR__ . '/../src/Support/ServiceHealthPresenter.php';

use HernandoTech\ServiceHealth\Support\ServiceHealthPresenter;

$presenter = new ServiceHealthPresenter();
$now = new DateTimeImmutable('2026-11-13 12:00:00 UTC');

function expect(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

$cases = [
    ['status' => 0, 'poll' => '-2 minutes', 'expected' => 'OK'],
    ['status' => 1, 'poll' => '-2 minutes', 'expected' => 'Warning'],
    ['status' => 2, 'poll' => '-2 minutes', 'expected' => 'Critical'],
    ['status' => 3, 'poll' => '-2 minutes', 'expected' => 'Unknown'],
    ['status' => 0, 'poll' => '-16 minutes', 'expected' => 'Stale'],
];

foreach ($cases as $case) {
    $row = $presenter->present([
        'service_id' => 7,
        'service_name' => 'Example',
        'service_status' => $case['status'],
        'service_message' => 'HTTP OK: 0.123 second response time',
    ], $now->modify($case['poll']), $now, 15);

    expect($row['status'] === $case['expected'], "Expected {$case['expected']}, got {$row['status']}");
    expect($row['responseTime'] === '123 ms', "Expected parsed response time, got {$row['responseTime']}");
}

$unknown = $presenter->present(['service_id' => 8], null, $now, 15);
expect($unknown['status'] === 'Unknown', 'A never-polled service must be unknown');
expect($unknown['lastPollAge'] === 'Never', 'A missing poll time must display Never');
expect($unknown['responseTime'] === null, 'A missing response time must remain null');

$disabled = $presenter->present(['service_disabled' => 1], $now, $now, 15);
expect($disabled['status'] === 'Disabled', 'A disabled service must display Disabled');

$ignored = $presenter->present(['service_ignore' => 1], $now, $now, 15);
expect($ignored['status'] === 'Ignored', 'An ignored service must display Ignored');

$responseCases = [
    'PING OK: time=24ms' => '24 ms',
    'DNS OK: latency=250us' => '0.25 ms',
    'TCP OK: response time=1.5s' => '1.5 s',
    'HTTP OK without timing data' => null,
];

foreach ($responseCases as $message => $expected) {
    $row = $presenter->present([
        'service_status' => 0,
        'service_message' => $message,
    ], $now, $now, 15);
    expect($row['responseTime'] === $expected, "Unexpected response time for: $message");
}

echo "ServiceHealthPresenter tests passed\n";
