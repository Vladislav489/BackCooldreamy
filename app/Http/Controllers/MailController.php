<?php

namespace App\Http\Controllers;

use App\Mail\SendMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller {
    public function send(Request $request) {
        if ($request->has('to')) {
            Mail::to($request->to)->send(new SendMail());
        }
    }
}
