<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('main');
});

Route::middleware('auth')->group(function(){

    //https://www.vidun.com.ua/zoho/redirect

    //Допуск по "Коду авторизації", отримаємо його 
    Route::get('zoho/redirect', function(){
        $query = http_build_query([
            'scope' => Config::get('zoho.scope'),
            'client_id' => Config::get('zoho.client_id'),
            'redirect_uri' => url('zoho/callback'),
            'response_type' => 'code',
            'access_type' => 'offline',
        ]);
        return redirect('https://accounts.zoho.com/oauth/v2/auth?'.$query);
    });

    //Отримаємо початкові токени по "Коду авторизації"
    Route::get('zoho/callback', function(Request $request){

        if($request->has('error'))
            return response()->json(['error' => 'Unknown error.'], 401);

        $expiresTime = new \DateTime();

        $http = new GuzzleHttp\Client;
        $response = $http->post('https://accounts.zoho.com/oauth/v2/token', [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'client_id' => Config::get('zoho.client_id'),
                'client_secret' => Config::get('zoho.client_secret'),
                'redirect_uri' => url('zoho/callback'),
                'code' => $request['code'],
            ],
        ]);

        if(is_null(Auth::user())){
            return response()->json(['error' => 'Authorisation Error.'], 401);
        }

        Config::set('zoho.user_id', Auth::user()->id);
        $thisUsersTokens = json_decode((string) $response->getBody(), true);
        $expiresTime->modify('+'.$thisUsersTokens["expires_in"].' sec');

        User::find(Auth::user()->id)->update([
            'acc_tkn' => $thisUsersTokens["access_token"], 
            'ref_tkn' => $thisUsersTokens["refresh_token"], 
            'expires_time' => $expiresTime->format('Y-m-d H:i:s')
        ]);   

        return response()->json(['ok' => 'Token successfully created.'], 200);
    });

    //https://www.vidun.com.ua/zoho/request-refresh

    Route::get('zoho/request-refresh', function(Request $request){

        if(is_null(Auth::user())){
            return response()->json(['error' => 'Authorisation Error.'], 401);
        }

        Config::set('zoho.user_id', Auth::user()->id);
        $user = User::find(Auth::user()->id);
        
        if(!$user)  
            return response()->json(['error' => 'User not found.'], 401);

        $expiresTime = new \DateTime();

        if(!is_null($user->expires_time)){
            $oldTima = new \DateTime($user->expires_time);
            if($oldTima > $expiresTime)
                return response()->json(['info' => 'The token is valid.'], 202);
        }

        $http = new GuzzleHttp\Client;
        $params = [
            'scope' => Config::get('zoho.scope'),
            'grant_type' => 'refresh_token',
            'client_id' => Config::get('zoho.client_id'),
            'client_secret' => Config::get('zoho.client_secret'),
            'redirect_uri' => url('zoho/callback'),
            'refresh_token' => $user->ref_tkn,
        ];

        $response = $http->post('https://accounts.zoho.com/oauth/v2/token', ['form_params' => $params]);    
        $thisUsersTokens = json_decode((string) $response->getBody(), true);
  
        if (array_key_exists('error', $thisUsersTokens))
            return response()->json(['error' => 'Token generation error.'], 401);

        $expiresTime->modify('+'.$thisUsersTokens["expires_in"].' sec');

        $user->update([
            'acc_tkn' => $thisUsersTokens["access_token"], 
            'expires_time' => $expiresTime->format('Y-m-d H:i:s')
        ]);
        return response()->json(['ok' => 'Token successfully created.'], 200);
    });
});

Auth::routes();
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
