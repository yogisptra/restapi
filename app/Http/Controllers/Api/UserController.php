<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator, DB;
use Carbon\Carbon;
use Laravel\Passport\Passport;
use Illuminate\Validation\Rule;

class UserController extends Controller
{

    public $successStatus = 200;

    public function index(Request $request)
    {
        try{
            // $data = User::findOrFail($id);
            $data = User::filter($request->only('search'))
                        ->paginate(2);

            return response([
                "status" => 1,
                "code" => http_response_code(),
                "message" => "Telah berhasil mengambil data!",
                "result" => [
                    'data' => $data
                ]
            ]);


        }catch(\Exception $exc){
             return response([
                "status" => 0,
                "code" => 400,
                "message" => "Tidak Ada Data!",
            ], 400);
        }
    }

    public function validator(Request $request, $id) {
        $rules = [
            'name'                  => 'required|string|max:100',
            'email'                 => [
                                        'required',
                                        'string',
                                         Rule::unique('users', 'email')->ignore($id)
                                    ],
            'password'              => [
                                        'required'.$id,
                                        'min:3'
                                    ],
            'confirm_password' => [
                                        'required'.$id,
                                        'same:password'
                                    ],
        ];

        $attributes = [
            'name'                  => 'Nama',
            'email'                 => 'Email',
            'password'              => 'Password',
            'confirm_password' => 'Konfirmasi Password',
        ];

        $messages = [
            'name.required' => ':attribute Wajib diisi',
            'name.string'   => 'Format :attribute Harus String',
            'name.max'  => ':attribute Melebihi :max Karakter',
            'email.required' => ':attribute Wajib diisi',
            'email.string'  => 'Format :attribute Harus String',
            'email.unique'  => ':attribute Sudah Terdaftar',
            'password.required' => ':attribute Wajib diisi',
            'password.min'  => ':attribute Minimal :max Karakter',
            'confirm_password.required' => ':attribute Wajib diisi',
            'confirm_password.same' => ':attribute Harus Sama Dengan Password',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        $validator->setAttributeNames($attributes); 


        return $validator;
    }

    public function login(){
        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){

            Passport::tokensExpireIn(Carbon::now()->addDays(30));
            Passport::refreshTokensExpireIn(Carbon::now()->addDays(60));

            $user = Auth::user();
            $objToken = $user->createToken('API_Token');
            $strToken = $objToken->accessToken;
            // $accessToken =  $user->createToken('token')->accessToken;

            $expired_token = $objToken->token->expires_at->diffInSeconds(Carbon::now());

            return response([
                "status" => 1,
                "code" => http_response_code(),
                "message" => "Telah berhasil Login!",
                "expires_in" => $expired_token,
                "result" => [
                    'access_token' => $strToken,
                    'data' => $user
                ]
            ]);
        }
        else{
             return response([
                "status" => 0,
                "code" => http_response_code(),
                "message" => "Gagal Login!",
            ]);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'comfirm_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);            
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken('nApp')->accessToken;
        $success['name'] =  $user->name;

        return response()->json(['success'=>$success], $this->successStatus);
    }

    

    public function store(Request $request)
    {
        try{
        $validator = $this->validator($request, $request->input('id'));

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);            
        }

        // $input = $request->all();
        // $input['password'] = bcrypt($input['password']);
        // $user = User::create($input);
        // $success['token'] =  $user->createToken('nApp')->accessToken;
        // $success['name'] =  $user->name;

        $data = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        return response([
                "status" => 1,
                "code" => http_response_code(),
                "message" => "Telah berhasil didaftarkan!",
                "result" => [
                    'data' => $data
                ]
            ]);
        }catch(\Exception $exc){
             return response([
                "status" => 0,
                "code" => 400,
                "message" => "Gagal didaftarkan!",
            ], 400);
        }
        
    }


    public function update(Request $request, $id)
    {
        try {
            $data = User::findOrFail($id);

            $validator = $this->validator($request, $id);

            if ($validator->fails()) {
                return response()->json(['error'=>$validator->errors()], 401);            
            }

            $data->update([
                    'name' => $request->name,
                    'email' => $request->email,
                ]);

            return response([
                "status" => 1,
                "code" => http_response_code(),
                "message" => "Telah berhasil diupdate!",
                "result" => [
                    'data' => $data
                ]
            ]);
        }catch(\Exception $exc){
             return response([
                "status" => 0,
                "code" => 400,
                "message" => "Gagal diupdate!",
            ], 400);
        }
            

    }

     public function show($id)
    {
        try{
            $data = User::findOrFail($id);
            // $data = User::all();

            return response([
                "status" => 1,
                "code" => http_response_code(),
                "message" => "Telah berhasil mengambil data!",
                "result" => [
                    'data' => $data
                ]
            ]);


        }catch(\Exception $exc){
             return response([
                "status" => 0,
                "code" => 400,
                "message" => "Tidak Ada Data!",
            ], 400);
        }
    }

    public function logout(Request $request)
    {
        $logout = $request->user()->token()->revoke();
        if($logout){
            return response()->json([
                'message' => 'Successfully logged out'
            ]);
        }
    }

    public function details()
    {
        $user = Auth::user();
        return response()->json(['success' => $user], $this->successStatus);
    }

    public function delete($id)
    {
        try{
           $data = User::findOrFail($id);
           $data->delete(); 
           return response([
                "status" => 1,
                "code" => http_response_code(),
                "message" => "Telah berhasil dihapus!",
            ]);
        }catch(\Exception $exc){
             return response([
                "status" => 0,
                "code" => 400,
                "message" => "Gagal dihapus!",
            ], 400);
        }
    }
}
