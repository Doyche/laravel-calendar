<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Spatie\GoogleCalendar\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class CalendarController extends Controller
{
    protected $client;
    protected $service;
    protected $calendarId = 'julija.pedroso@gmail.com';

    public function __construct(Request $request)
    {
        $client = new \Google_Client();
        $client->setApplicationName('Laravel Google Calendar');
        $client->setScopes([
            \Google_Service_Calendar::CALENDAR,
            \Google_Service_Calendar::CALENDAR_EVENTS,
        ]);
        $client->setAuthConfig(storage_path('app/google-calendar/credentials.json'));
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        $tokenPath = storage_path('app/google-calendar/token.json');
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // Request authorization from the user.
                if (!$request->has('code')) {
                    $authUrl = $client->createAuthUrl();
                    return Redirect::to($authUrl)->send();;
                }

                $authCode = $request->query('code');

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new \Exception(join(', ', $accessToken));
                }
            }
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }

        $this->client = $client;

        $service = new \Google_Service_Calendar($this->client);
        $this->service = $service;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $optParams = array(
            /*'maxResults' => 10,
            'orderBy' => 'startTime',
            'singleEvents' => true,
            'timeMin' => date('c'),*/
        );

        $results = $this->service->events->listEvents($this->calendarId, $optParams);
        $events = $results->getItems();

        return $events;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required',
            'email' => 'required',
            'time' => 'required',
            'date' => 'required',
        ]);

        $validator = validator()->make(
            ['akismet' => 'akismet'],
            [
                'akismet' => [
                    new \nickurt\Akismet\Rules\AkismetRule(
                        request()->input('name'),
                        request()->input('phone'),
                        request()->input('email'),
                        request()->input('time'),
                        request()->input('date')
                    )
                ]
            ]
        );

        if ($validator->fails()) {
            return response(['message'=>'Invalid data!']);
        }

        $datetime = $request->date." ".$request->time;

        $evt = new \Google_Service_Calendar_Event;
        $evt->setStart(new \Google_Service_Calendar_EventDateTime(['dateTime' => Carbon::createFromFormat('Y-m-d H:i', $datetime, 'Europe/Belgrade')]));
        $evt->setEnd(new \Google_Service_Calendar_EventDateTime(['dateTime' => Carbon::createFromFormat('Y-m-d H:i', $datetime, 'Europe/Belgrade')->addHour()]));
        $evt->setSummary($request->name." ".$request->phone);

        $reminders = new \Google_Service_Calendar_EventReminders();
        $reminders->setUseDefault(false);

        //15min
        $reminder = new \Google_Service_Calendar_EventReminder();
        $method = empty($v['method']) ? 'email' : $v['method'];
        $reminder->setMethod($method);
        $minute = empty($v['minute']) ? '15' : $v['minute'];
        $reminder->setMinutes($minute);

        //30
        $reminder2 = new \Google_Service_Calendar_EventReminder();
        $method2 = empty($v['method']) ? 'email' : $v['method'];
        $reminder2->setMethod($method2);
        $minute2 = empty($v['minute']) ? '30' : $v['minute'];
        $reminder2->setMinutes($minute2);

        $reminders->setOverrides([$reminder, $reminder2]);
        $evt->setReminders($reminders);

        $evt->setAttendees(
            [
                new \Google_Service_Calendar_EventAttendee(['email' => $request->email]),
            ]
        );

        $optParams = Array(
            'sendNotifications' => true,
        );

        $this->service->events->insert($this->calendarId, $evt, $optParams);

        return response(['message'=>'Success!']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
