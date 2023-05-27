<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestEmail; 
use DB;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

// namespace App\Models;

class AuthController extends Controller
{

  public function sendMail($email, $token, $msg)
  {
        $mailData = [
              "email" => $email,
              'code' => $token,
              'msg'=>$msg,
          ];
      
          Mail::to($mailData['email'])->send(new TestEmail($mailData));
      
        
  }

      public function singleSMS($phone, $msg)
      {
            $new_format = '+234';
        for ($i=0; $i < strlen($phone) ; $i++) { 
            if ($i>0) {
              $new_format .= $phone[$i];
            }
        }
        $phone = $new_format;
      $response = Http::get("your sms provider route link", [
            "headers" =>[
                'Accept'=>'application/json',
                'Content-Type' =>'application/x-www-form-urlencoded',
            ],
              "apikey"=>"your api key",
              "username"=>"your username",
              "sender"=>"your sender name",
              "recipients"=>$phone,
              "messagetext"=>$msg,
              "flash" => 0,
          ]);

      
      }

      
      //send token function
      public function sendCode($email, $phone)
      {
            $code = str_shuffle('1234567890232819382');
            $token = substr($code,0,6);
            $user = auth('sanctum')->user();
            $msg = 'Use the following OTP to complete your Sign Up procedures. OTP is valid for 5 minutes';
            $message = "Thank you for choosing Ushescrow.! Use the following OTP to complete your Sign Up procedures. OTP is valid for 5 minutes $token";
            $this->singleSMS($phone, $message);
           
            $this->sendMail($email, $token, $msg);
            
            $check = DB::table('password_resets')->where('email', $email)->orWhere('email', $phone)->delete();

            $insert = DB::table('password_resets')->insert([
               'email' => $email,
               'token' => $token,
            ]);

            return response([
                  'code'=>'0',
                  'message'=>'Verification Code has being sent!',
                  'header' => 'Success!',
              ]);
      }


    public function Signup(Request $req)
    {
        
          //Payload validations
       $req->validate([
        'fullName'=>'required|string',
        'phoneNumber'=>'required|max:11|min:11|unique:users,phone',
        'email'=>'required|email|unique:users,email',
        'password' => 'min:6',
        'confirm_password' => 'required_with:password|same:password|min:6'

       ]);

       //Validating Image Profile
       if ($req->profile=='undefined') {
         abort(422, "Profile Field Is required");
       }

       //validating to numbers only
       if (!preg_match("/^[0-9 ]*$/",$req->phoneNumber)) {
         abort(422, "Invalid Phone Number");
       }
      
       //confirm referral code 
       if (!empty($req->referred)) {
      

        $user = User::where('refCode', $req->referred)->first();
        if (empty($user->name)) {
         abort(422, "Referral Code Does not Exist");
        }
       }

        //generate Referral code
        $refcode = str_shuffle('1234567890232819382');
        $refcode = substr($refcode,0,6);
 
        //store image on public folder
        $profile = explode('c/',$req->profile->store('public'));
 
        //Insert data to users table
         $Insert = User::create([
             'name'=> $req->fullName,
             'phone'=> $req->phoneNumber,
             'email'=> $req->email,
             'password'=>Hash::make($req->password),
             'profile'=> $profile[1],
             'userType'=> "user",
             'refCode'=> $refcode,
             'referred'=> $req->referred?$req->referred:'0',
             'created_at'=>Carbon::now(),
             'updated_at'=>Carbon::now(),
          ]);
 
         //Success response
         if ($Insert) {
            $this->sendCode($req->email, $req->phoneNumber);
           $user = User::where('phone',$req->phoneNumber)->first();
           DB::table('personal_access_tokens')->where('name',$req->phoneNumber)->orwhere('name',$req->email)->delete();
           $token = $user->createToken($req->email)->plainTextToken;
           Auth::guard('web')->login($user);
             return response([
                 'data'=>[
                   'userInfo'=>$user,
                   'token'=>$token,
                 ],
                 'code'=>'0',
                 'message'=>'Account Has Being Created Successfully',
                 'header' => 'Success!',
             ]);
         }

      
    }

    //Login route
    public function login(Request $request)
    {
      //auth()->guard('user')->id()
       $fields = $request->validate([
         'phone'=>'required',
         'password'=>'required',
       ]);
        $user = User::where('phone',$fields['phone'])->orwhere('email',$fields['phone'])->first();
        if (!$user || !Hash::check($fields['password'], $user->password)) {
           abort(400, 'Invalid Login Details');
        }
        DB::table('personal_access_tokens')->where('name',$request->phone)->delete();
          $token = $user->createToken($fields['phone'])->plainTextToken;
          Auth::guard('web')->login($user);
          return response([
            'data'=>[
              'userInfo'=>$user,
              'token'=>$token,
            ],
            'code'=>'0',
            'message'=>'Logged In Successfully',
            'header' => 'Success!',
        ]);
  
  
      }

      //Logout Function -- Deleting Stored token
      public function logout()
      {
        auth()->user()->tokens()->delete();
      }

      //Update users data
      public function UpdateDatas(Request $req)
      {
        $user = auth('sanctum')->user();
        $new_email=$req->email;
        $new_phone=$req->phoneNumber;

        $req->validate([
          'fullName' => 'Required|string',
          'phoneNumber' => 'Required',
          'email' => 'Required|email',
        ]);

        $checkData = User::where('id', auth('sanctum')->user()->id)->first();
        if ($checkData->phone != $req->phoneNumber) {
          $emailCheck = User::where('phone', $req->phoneNumber)->first();
          if ($emailCheck) {
           abort(403, "Phone Number already in use");
          }else{
            if ((!$req->has('otp') || $req->otp=='undefined') ) {
              $code = str_shuffle('1234567890232819382');
            $token = substr($code,0,6);
           $message = "Thank you for choosing Ushescrow.! Use the following OTP to complete your verification. OTP is valid for 5 minutes $token";
           $this->singleSMS($req->phoneNumber, $message);
            
            $check = DB::table('password_resets')->where('email', $user->email)->orWhere('email', $user->phone)->delete();

            $insert = DB::table('password_resets')->insert([
               'email' => $user->email,
               'token' => $token,
            ]);

            return response([
                  'code'=>'1',
                  'message'=>'Verification Code has being sent to '.$req->phoneNumber,
                  'header' => 'Success!',
              ]);
            }else{
              
              $check = DB::table('password_resets')->where('email', $user->email)->orWhere('email', $user->phone)->first();
              if ($check && $check->token==$req->otp) {
                $check = DB::table('password_resets')->where('email', $user->email)->orWhere('email', $user->phone)->delete();
                User::where('id',auth('sanctum')->user()->id)->update([
                  'phone' => $new_phone,
                ]);
              }else{
                 abort(422, 'Invalid OTP confirmation');
              }
            }
          }
        }

        
        if ($checkData->email != $req->email) {
          $emailCheck = User::where([['email', $req->email],['id', '!=' ,auth('sanctum')->user()->id]])->first();
          if ($emailCheck) {
           abort(403, "Email address already in use");
          }else{
            if (!$req->has('otp') || $req->otp=='undefined') {
              $code = str_shuffle('1234567890232819382');
            $token = substr($code,0,6);
            $msg = 'Use the following OTP to complete your Sign Up procedures. OTP is valid for 5 minutes';
            $this->sendMail($req->email, $token, $msg);
            
            $check = DB::table('password_resets')->where('email', $user->email)->orWhere('email', $user->phone)->delete();

            $insert = DB::table('password_resets')->insert([
               'email' => $user->email,
               'token' => $token,
            ]);

            return response([
                  'code'=>'1',
                  'message'=>'Verification Code has being sent to '.$req->email,
                  'header' => 'Success!',
              ]);
            }else{
              
              $check = DB::table('password_resets')->where('email', $user->email)->orWhere('email', $user->phone)->first();
              if ($check && $check->token==$req->otp) {
                  $new_email = $req->email;
                $check = DB::table('password_resets')->where('email', $user->email)->orWhere('email', $user->phone)->delete();
                User::where('id',auth('sanctum')->user()->id)->update([
                  'email' => $new_email,
                ]);
              }else{
                 abort(422, 'Invalid OTP confirmation');
              }
            }
          }
        }


        if ($req->profile == 'undefined' || $req->profile == '') {
          $profile = auth('sanctum')->user()->profile;
        }else{
           $profile = explode('c/',$req->profile->store('public'))[1];
         }

         User::where('id',auth('sanctum')->user()->id)->update([
          'name' => $req->fullName,
          'profile' => $profile,

         ]);
         return response([
          'code'=>'0',
          'message'=>'Account Has Being Updated Successfully',
          'header' => 'Success!',
        ]);
      }

     
}
