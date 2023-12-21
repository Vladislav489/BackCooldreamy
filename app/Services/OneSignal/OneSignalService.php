<?php

namespace App\Services\OneSignal;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;

class OneSignalService
{
    public function addToken($token)
    {
        $user = Auth::user();
        try {
            $user->update([
                'onesignal_token' => $token
            ]);
            $user->save();
            return 'success';
        } catch (\Throwable $e) {
            return 'error: ' . $e->getMessage();
        }
    }

    static public function sendNotification($to, $title, $message, $img = null)
    {
        $app_id = env('ONESIGNAL_APP_ID');
        $onesignal_key = env('ONESIGNAL_REST_API_KEY');
        $content = ['en' => $message];
        $headings = ['en' => $title];
        if (is_null($img)) {
            $fields = [
                'app_id' => $app_id,
                'headings' => $headings,
                'include_player_ids' => array($to),
                'content_available' => true,
                'contents' => $content
            ];
        } else {
            $fields = [
                'app_id' => $app_id,
                'headings' => $headings,
                'include_player_ids' => array($to),
                'content_available' => true,
                'contents' => $content,
                'big_picture' => $img
            ];
        }
        $headers = [
            'Authorization' => 'Basic ' . $onesignal_key,
            'content-type' => 'application/json',
            'accept' => 'application/json',
        ];

        $client = new Client();
        $response = $client->request('POST', 'https://onesignal.com/api/v1/notifications', [
            'body' => json_encode($fields),
            'headers' => $headers
        ]);

        return $response->getBody();
    }
}
