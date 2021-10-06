<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    // Очікуємо POST json 
    // {
    // "name": "Kotenko",
    // "password": "kotenko15",
    // "email": "kotenko@ukr.net",
    // "device_name": "kotenkotest"
    // }
    public function register(Request $request)
    { 
        $validator = \Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'device_name' => ['required', 'string']
        ]); 
        
        if($validator->fails())
            return response()->json(['error' => $validator->errors()], 401);
                
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $token = $user->createToken($request->device_name)->plainTextToken;
        return response()->json(['token' => $token], 200);
    }

    public function token(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'device_name' => ['required', 'string']
        ]);    
        
        if($validator->fails())
            return response()->json(['error' => $validator->errors()], 401);
     
        $user = User::where('email', $request->email)->first();
     
        if(!$user || !Hash::check($request->password, $user->password)) 
            return response()->json(['error' => 'The provided credentials are incorrect.'], 401);
     
        return response()->json(['token' => $user->createToken($request->device_name)->plainTextToken], 200);
    }

    public function newDeal(Request $request){

        $validator = \Validator::make($request->all(), [
            "deal_name" => "required|string|max:100",
            "owner_id" => "required|integer",
            "account_id" => "required|integer",
            "contact_id" => "required|integer",
            "company_id" => "required|integer",
            "description" => "required|string|max:300",
            "amount" => "required|regex:/^\d+(\.\d{1,2})?$/",
            "close_date" => "required|date|date_format:Y-m-d",
        ]); 

        if($validator->fails())
            return response()->json(['error' => $validator->errors()], 401);

        $user = User::find(\Config::get('zoho.user_id'));

        if(!$user)  
            return response()->json(['error' => 'User not found.'], 401);

        $http = new \GuzzleHttp\Client;

        $arrBody = [
            "Owner" => [
                "id" => $request['owner_id']
            ],
            "Account_Name" => [
                "id" => $request['account_id']
            ],
            "Contact_Name" => [
                "id" => $request['contact_id']
            ],
            "Campaign_Source" => [
                "id" => $request['company_id']
            ],
            "Type" => "New Business",
            "Description" => $request['description'],
            "Deal_Name" => $request['deal_name'],
            "Amount" => (string)round($request['amount'], 2),
            "Next_Step" => "Next_Step",
            "Stage" => "Needs Analysis",
            "Lead_Source" => "Cold Call",
            "Closing_Date" => $request['close_date']
        ];

        $response = $http->post('https://www.zohoapis.com/crm/v2/Deals', [
            'headers' => [
                'Authorization' => "Zoho-oauthtoken ".$user->acc_tkn,
                'Content-Type' => 'application/json',
            ],
            'body' => '{
                "data": [
                    '.json_encode($arrBody).'
                ]                        
            }'
        ]);

        $thisNewDeal = json_decode((string) $response->getBody(), true);

        if (array_key_exists('error', $thisNewDeal))
            return response()->json(['error' => 'Error creating deal.'], 401);
        else
            return $thisNewDeal;
    }
    //#############################################################
    public function newTask(Request $request){
        $validator = \Validator::make($request->all(), [
            "subject" => "required|string|max:100",
            "owner_id" => "required|integer",
            "contact_id" => "required|integer",
            "deal_id" => "required|integer",
            "description" => "required|string|max:300",
            "priority" => "required|string|max:50",
            "due_date" => "required|date|date_format:Y-m-d",
        ]); 

        if($validator->fails())
            return response()->json(['error' => $validator->errors()], 401);

        $user = User::find(\Config::get('zoho.user_id'));

        if(!$user)  
            return response()->json(['error' => 'User not found.'], 401);
        
        $http = new \GuzzleHttp\Client;

        $response = $http->post('https://www.zohoapis.com/crm/v2/Tasks', [
            'headers' => [
                'Authorization' => "Zoho-oauthtoken ".$user->acc_tkn,
                'Content-Type' => 'application/json',
            ],
            'body' => '{
                "data": [
                    {
                        "Owner": {
                            "id": "'.$request['owner_id'].'"
                        },
                        "Who_Id": {
                            "id": "'.$request['contact_id'].'"
                        },
                        "What_Id": {
                            "id": "'.$request['deal_id'].'"
                        },
                        "$se_module": "Deals",
                        "Status": "In Progress",
                        "Send_Notification_Email": false,
                        "Description": "'.$request['description'].'",
                        "Due_Date": "'.$request['due_date'].'",
                        "Priority": "'.$request['priority'].'",
                        "send_notification": false,
                        "Subject": "'.$request['subject'].'",
                        "Remind_At": {
                            "ALARM": "FREQ=NONE;ACTION=EMAIL;TRIGGER=DATE-TIME:2021-10-25T17:09:00+05:30"
                        }
                    }
                ]
            }'
        ]);

        //"Remind_At": null
        // "Remind_At": {
        //     "ALARM": "FREQ=NONE;ACTION=EMAIL;TRIGGER=DATE-TIME:2021-01-25T17:09:00+05:30"


        $thisNewTask = json_decode((string) $response->getBody(), true);

        if (array_key_exists('error', $thisNewTask))
            return response()->json(['error' => 'Error creating task.'], 401);
        else
            return $thisNewTask;
    }
}
