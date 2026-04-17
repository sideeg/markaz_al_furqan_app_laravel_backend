<?php
// Path: app/Jobs/PushFcmNotification.php

namespace App\Jobs;

use App\Models\DeviceToken;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class PushFcmNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $target;
    protected $title;
    protected $message;
    protected $data;

    public function __construct($target, string $title, string $message, array $data = [])
    {
        $this->target  = $target;
        $this->title   = $title;
        $this->message = $message;
        $this->data    = $data;
    }

    public function handle(Messaging $messaging): void
    {
        try {
            $notification = Notification::create($this->title, $this->message);

            // FIX: FCM data must always contain 'type' so Flutter can route correctly.
            // Previously 'type' was only stored in the DB Notification model but
            // never sent in the FCM data payload — Flutter received data['type'] == null
            // and fell through to the default case (dashboard/notifications screen).
            //
            // The 'type' field comes from the NotificationService $data array.
            // For broadcasts (teachers/students/both), the caller should pass
            // ['type' => 'new_student'] etc. inside $data.
            // For safety, we also ensure it is always a string.
            $dataWithType = array_merge(
                ['type' => 'custom_broadcast'], // safe default
                $this->data
            );
            // FCM requires all data values to be strings
            $stringifiedData = array_map('strval', $dataWithType);

            // ── Android config ────────────────────────────────────────────────
            $androidConfig = AndroidConfig::fromArray([
                'priority' => 'high',
                'notification' => [
                    'channel_id'              => 'sheikh_alerts',
                    'default_sound'           => true,
                    'default_vibrate_timings' => true,
                ],
            ]);

            // ── iOS config ────────────────────────────────────────────────────
            $apnsConfig = ApnsConfig::fromArray([
                'headers' => ['apns-priority' => '10'],
                'payload' => ['aps' => ['sound' => 'default', 'badge' => 1]],
            ]);

            // ── Topic broadcast ───────────────────────────────────────────────
            if (in_array($this->target, ['students', 'teachers', 'both'])) {
                $topics = $this->target === 'both'
                    ? ['students', 'teachers']
                    : [$this->target];

                foreach ($topics as $topic) {
                    $msg = CloudMessage::withTarget('topic', $topic)
                        ->withNotification($notification)
                        ->withData($stringifiedData)
                        ->withAndroidConfig($androidConfig)
                        ->withApnsConfig($apnsConfig);
                    $messaging->send($msg);
                    Log::info("FCM topic sent to: {$topic} with type: {$stringifiedData['type']}");
                }
                return;
            }

            // ── Individual users ──────────────────────────────────────────────
            $tokens = DeviceToken::whereIn('user_id', (array) $this->target)
                ->pluck('fcm_token')
                ->toArray();

            if (empty($tokens)) {
                Log::info('FCM: No tokens for users: ' . json_encode($this->target));
                return;
            }

            $msg = CloudMessage::new()
                ->withNotification($notification)
                ->withData($stringifiedData)
                ->withAndroidConfig($androidConfig)
                ->withApnsConfig($apnsConfig);

            $report = $messaging->sendMulticast($msg, $tokens);
            Log::info("FCM multicast — success: {$report->successes()->count()}, failed: {$report->failures()->count()}, type: {$stringifiedData['type']}");

            if ($report->hasFailures()) {
                $invalidTokens = [];
                foreach ($report->failures()->getItems() as $failure) {
                    $invalidTokens[] = $failure->target()->value();
                }
                if (!empty($invalidTokens)) {
                    DeviceToken::whereIn('fcm_token', $invalidTokens)->delete();
                    Log::info('FCM: Removed ' . count($invalidTokens) . ' invalid token(s)');
                }
            }
        } catch (\Exception $e) {
            Log::error('FCM Notification Failed: ' . $e->getMessage());
            throw $e;
        }
    }
}