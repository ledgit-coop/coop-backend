<?php

namespace App\Http\Controllers;

use App\Mail\PublicFeedbackMailable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PublicController extends Controller
{

    public function mail(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required',
            'subject' => 'required',
            'message' => 'required',
        ]);

        Mail::to(config('koop.public.receiver_email'))
            ->cc(config('koop.public.carbon_copy'))
            ->send(new PublicFeedbackMailable(...[$request->name, $request->email, $request->subject, $request->message]));

        return response('OK');
    }
    
}
