<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Report;
use App\Voice;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReportCase;
use GuzzleHttp\Exception\RequestException;


class MailController extends Controller
{
    
    public function __construct() 
    {
    	$this->middleware('auth:sanctum');
    }

    public function mail(Request $request, $id) {

    	$report = \App\Report::find($id);

        $report_description = $report->description;
        $imageUrl = $report->media_url['images'];
        $videoUrl = $report->media_url['videos'];
        $reportUrl = 'https://voiceout.netlify.app/report/' . $report->id;

        if ($imageUrl) {   
            $imageUrl = $imageUrl[0];
        }

        if ($videoUrl) {
            $videoUrl = $videoUrl[0];
        }

        $recipients = $request->recipients;
        // dd($recipients);
        foreach ($recipients as $recipient) {
        	$voice = \App\Voice::find($recipient);
        	$report->voices()->attach($voice->id);
        	try {
        		Mail::to($voice->email)->send(new ReportCase($report_description, $imageUrl, $videoUrl, $reportUrl));
        	} catch (RequestException $e) {
        		continue;
        	}
        }

        $response = [
            'message' => 'email sent successfully'
        ];

        return response()->json($response);
    }

}
