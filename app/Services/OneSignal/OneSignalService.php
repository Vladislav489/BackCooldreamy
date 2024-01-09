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
            $user->onesignal_token = $token;
            $user->save();
            return 'success';
        } catch (\Throwable $e) {
            return 'error: ' . $e->getMessage();
        }
    }

    static public function sendNotification($to, $title, $message, $img = null)
    {
        $app_id = 'ec3e25a7-ac77-4b41-80f7-c892c1edba15';
        $onesignal_key = 'NGE5ZDY0YmYtYmE1ZS00NDBiLTkwOTYtYmJiN2U2NTBlZDJh';
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
        $headers = ['Authorization' => 'Basic ' . $onesignal_key, 'content-type' => 'application/json', 'accept' => 'application/json',];

        $client = new Client();
        $response = $client->request('POST', 'https://onesignal.com/api/v1/notifications', ['body' => json_encode($fields), 'headers' => $headers]);

        return $response->getBody();
    }

    //TODO
    static public function sendMail($sender, $user, $message)
    {
        $app_id = env('ONESIGNAL_APP_ID');
        $onesignal_key = env('ONESIGNAL_REST_API_KEY');
        $fields = [
            'app_id' => $app_id,
            'include_player_ids' => array($user),
            'email_subject' => 'Test',
            'email_body' => view('mail.new_message', compact('sender', 'user', 'message'))->render()
        ];
        $headers = ['Authorization' => 'Basic ' . $onesignal_key, 'content-type' => 'application/json', 'accept' => 'application/json',];

        $client = new Client();
        $response = $client->request('POST', 'https://onesignal.com/api/v1/notifications', ['body' => json_encode($fields), 'headers' => $headers]);

        return $response->getBody();
    }
}
