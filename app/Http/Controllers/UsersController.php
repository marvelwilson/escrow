<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestEmail; 
use Auth;
use DB;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UsersController extends Controller
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
      public function sendCode()
      {
            $code = str_shuffle('1234567890232819382');
            $token = substr($code,0,6);
            $user = auth('sanctum')->user();
            $msg = 'Use the following OTP to complete your Sign Up procedures. OTP is valid for 5 minutes';
            $message = "Thank you for choosing Ushescrow.! Use the following OTP to complete your Sign Up procedures. OTP is valid for 5 minutes $token";
            $this->singleSMS($user->phone, $message);
           
            $this->sendMail($user->email, $token, $msg);
            
            $check = DB::table('password_resets')->where('email', $user->email)->orWhere('email', $user->phone)->delete();

            $insert = DB::table('password_resets')->insert([
               'email' => $user->email,
               'token' => $token,
            ]);

            return response([
                  'code'=>'0',
                  'message'=>'Verification Code has being sent!',
                  'header' => 'Success!',
              ]);
      }

           //send Reset code function
           public function resecode($user)
           {
                 $code = str_shuffle('1234567890232819382');
                 $token = substr($code,0,6);
                 $msg = "Thank you for choosing Ushescrow.! Use the following Reset Code for change of password.";
                 $message = "Thank you for choosing Ushescrow.! Use the following Reset code for change of password. $token";
                 $this->singleSMS($user->phone, $message);
                
                 $this->sendMail($user->email, $token, $msg);
                 
                 $check = DB::table('password_resets')->where('email', $user->email)->orWhere('email', $user->phone)->delete();
     
                 $insert = DB::table('password_resets')->insert([
                    'email' => $user->email,
                    'token' => $token,
                 ]);
     
                
           }

      //VerifyCode and update users status Function
      public function VerifyCode(Request $req)
      {
            $user = auth('sanctum')->user();
            $check = DB::table('password_resets')->where('email', $user->email)->orWhere('email', $user->phone)->first();
            if ($check && $check->token==$req->otp) {
                User::FindOrfail($user->id)->update(['status'=>'Verified']);
              $check = DB::table('password_resets')->where('email', $user->email)->orWhere('email', $user->phone)->delete();
                return response([
                  'code'=>'0',
                  'message'=>'OTP Verification Was Successful!',
                  'header' => 'Success!',
              ]);
            }else{
               abort(422, 'Invalid OTP confirmation');
            }
      }

       //Reset Password
       public function resetPassword(Request $req)
       {
            //send reset code area
           
            if ($req->has('email') && !$req->has('code')) {
               $req->validate([
                  'email'=>'required|email',
               ]);

              $user = User::where('phone',$req->email)->orwhere('email', $req->email)->first();
              if ($user) {
                  $this->resecode($user);
                  return response([
                        'code'=>'0',
                        'message'=>'Verification Code has being sent!',
                        'header' => 'Success!',
                        'data'=>[
                              'userInfo'=> $user,
                        ]
                    ]);
              }else{
                 abort(403, 'Invalid Creds');
              }
             
            }

            //reset area
            if ($req->has('code')) {
                  $req->validate([
                        'code'=>'required',
                        'password' => 'min:6',
                        'confirm_password' => 'required_with:password|same:password|min:6'

                     ]);
                     
                     $user = User::where('phone',$req->email)->orwhere('email', $req->email)->first();
                     $check = DB::table('password_resets')->where('email', $user->email)->first();
                     
                     if ($check->token==$req->code) {
                         User::FindOrfail($user->id)->update([
                              'password'=>Hash::make($req->password)
                        ]);
                       $check = DB::table('password_resets')->where('email', $user->email)->delete();
                         return response([
                           'code'=>'1',
                           'message'=>'Password Reset Was Successful!',
                           'header' => 'Success!',
                           ''
                       ]);
                     }else{
                        abort(422, 'Invalid Reset Code');
                     }
            }
            //  $user = auth('sanctum')->user();
          
 
       }

        //Get User Info Function
       public function getUserInfo()
       {
            $notify = DB::table('notifications')->join('users','notifications.initiator_id','users.id')->where('user_id',auth('sanctum')->user()->email)->latest('notifications.created_at')->get();

            return response([
                  'code'=>'0',
                  'message'=>'Found',
                  'header' => 'Success!',
                  'data'=>[
                        'userInfo'=> auth('sanctum')->user(),
                        'notify' => $notify,
                  ],
              ]);
       }

       //Start Ushescrow Transaction

       public function pairUser(Request $req)
       {
            if ($req->has('valide') && !$req->has('status')) {
                  $code = str_shuffle('1234567890232819382');
                  $ref = substr($code,0,10);
                  $req->validate([
                        'valide'=>'required',
                     ]);
                     
                    $user = User::where('phone',$req->valide)->orwhere('email', $req->valide)->orwhere('refCode', $req->valide)->first();
                    if (!empty($user->email) && ($user->email != auth('sanctum')->user()->email)) {
                        
                        return response([
                              'code'=>'0',
                              'message'=>'Pairing Successful',
                              'header' => 'Success!',
                              'data'=>[
                                    'userInfo'=> $user,
                                    'ref' => $ref,
                              ]
                          ]);
                 }else{
                  $msg="Hi Friend, I am using USH Escrow for all my transaction and
                  would want you too to try it.
                  USH escrow provides your transaction a surety as it will
                  ensure your money get the full value and your services is
                  fully paid.
                  Use this link to download and start using USH Escrow
                  today, by also using the referral code ".auth('sanctum')->user()->refCode.". You can also visit www.USHescrow.com to register
                  and start using the app";
                  if (strlen($req->valide)>11) {
                    $this->sendMail($req->valide, '', $msg);
                  }else{
                    $this->singleSMS($req->valide, $msg);
                  }
                  abort(404, "Cred Not Found. But Referral link has being sent successfully");
                 }
               
       }elseif ($req->has('status')) {
            $user = User::where('phone',$req->valide)->orwhere('email', $req->valide)->orwhere('refCode', $req->valide)->first();
           
            $req->validate([
                  'status'=>'required',
                  'amount'=>'required|int',
                  'installment'=>'required',

            ]); //Validate Request
             
            //Confirm Amount
            $new_balance = 0;
            
            if ($req->status == 'Customer') {
                  if (auth('sanctum')->user()->amount < $req->amount) {
                     abort(403, "Insuficient Customer's wallet Balance");
                  }else{
                     $new_balance = auth('sanctum')->user()->amount - $req->amount;
                  }
              }
              if ($req->status == 'Vendor') {
                 if ($user->amount < $req->amount) {
                  abort(403, "Insuficient Customer's wallet Balance");
                 }else{
                  $new_balance = $user->amount - $req->amount;
                 }
              }
            $a=1;
            
            while ($a <= $req->installment) {
                  $req->validate([
                        'percent_'.$a => 'required|int',
                        'desc_'.$a => 'required'
                  ]); //Validate percents & description
            $a++; 
            }
            $total_percent=$req->percent_1+$req->percent_2+$req->percent_3;
            
            if ($total_percent > 100) {
                 abort(422, "Total Percent can not Exceed 100");
            }
            if ($req->invoice == 'undefined') {
                  abort(422, "invoice is required");
            }

            $req->validate([
                  'service'=>'required',
                  'desc'=>'required',
                  'date'=>'required',
                  'acc_number'=>'required|int',
                  'bname'=>'required',
                  'bhname'=>'required',
            ]); //Validate Request contiuniation

            //store Invoice Image on public folder
            $invoice = explode('c/',$req->invoice->store('public'));
            $percent= ['percent_1'=>$req->percent_1, 'percent_2'=>$req->percent_2, 'percent_3'=>$req->percent_3, 'desc_1'=>$req->desc_1, 'desc_2'=>$req->desc_2, 'desc_3'=>$req->desc_3, 'paid_1'=>'', 'paid_2'=>'', 'paid_3'=>''];
            $percents = json_encode($percent);
             DB::table('transactions')->insert([
                  'user_id'=>auth('sanctum')->user()->id,
                  'user_status'=>$req->status,
                  'paired_id'=>$req->valide,
                  'amount'=>$req->amount,
                  'installment'=>$req->installment,
                  'invoice'=>$invoice[1],
                  'service'=>$req->service,
                  'desc'=>$req->desc,
                  'date'=>$req->date,
                  'acc_number'=>$req->acc_number,
                  'bname'=>$req->bname,
                  'bankcode'=>$req->code,
                  'bhname'=>$req->bhname,
                  'percent'=>$percents,
                  'ref_id'=>$req->ref,
                  'next'=>0,
                  'status'=>'pending',
                  'payment_status'=>'upaid',
                  'created_at'=>Carbon::now(),
                  'updated_at'=>Carbon::now(),

             ]);
             $status = $req->status=='Customer'?'Vendor':'Customer';
             $message = auth('sanctum')->user()->name ."(Ref: $req->ref) has requested to pair with you. You will be
             $status in this escrow transaction ($req->service)";
             
             DB::table('notifications')->insert([
                  'user_id'=>$req->valide,
                  'type'=>'request',
                  'message'=>$message,
                  'initiator_id'=>auth('sanctum')->user()->id,
                  'n_status'=>'unred',
                  'created_at' => Carbon::now(),
                  'updated_at' => Carbon::now(),
             ]);
             $token = '';
             $userPhone = User::where('email', $req->valide)->first();
             $this->singleSMS($userPhone->phone, $message);
             $this->sendMail($userPhone->email, $token, $message);

             //Update Customer's Account Balance
             if ($req->status == 'Customer') {
                  User::Where('id', auth('sanctum')->user()->id)->update([
                        'amount' => $new_balance
                     ]);
              }elseif ($req->status == 'Vendor') {
                  User::Where('email',$user->email)->update([
                        'amount' => $new_balance
                     ]);
              }

             return response([
                  'code'=>'1',
                  'message'=>'Transaction has being Created. Pairing partner will receive a notification shortly!',
                  'header' => 'Success!',
              ]);

          }else{
            abort(422, "Could Not Proccess Your Request");
      }
      }

      //Get pending ushescrow transactions
     public function getPending(){
        
      $transactions = DB::table('transactions')->where([['paired_id', auth('sanctum')->user()->email]])->orwhere([['user_id', auth('sanctum')->user()->id]])->latest('transactions.created_at')->get();
      $history = DB::table('deposits')->where('user_id', auth('sanctum')->user()->id)->get();
      return response([
            'code'=>'0',
            'message'=>'Found',
            'header' => 'Success!',
            'data'=>[
                  'transactions'=>$transactions,
                  'histories' => $history,
            ]
        ]);
      }

      //All Request Funtionality route
      public function request(Request $req)
      {
              $txn = DB::table('transactions')->where('id', $req->id)->first();

              $pairing = User::where('email', $txn->paired_id)->first(); //Pairing 
              $requester = User::where('id', $txn->user_id)->first(); //Requester

              $msg='';
              $resMsg;
            //Rejection Functionality
            if ($req->stats=='Reject'){
                  $update = DB::table('transactions')->where('id',$req->id)->update([
                        'stats'=> $req->stats,
                        'status'=> $req->stats,
                        'reason'=> $req->reason,
                        ]);
                  $msg = "Your pairing request have been rejected by $pairing->name (Ref: $txn->ref_id) due to the reason below\n $req->reason";
                  $resMsg = "You have rejected pairing with $pairing->name  (Ref: $txn->ref_id) due to the reason below  $req->reason";
                  //Update Customer's Account Balance
             if ($txn->user_status == 'Customer') {
                  User::Where('id',$requester->id)->update([
                        'amount' => $txn->amount + $requester->amount,
                     ]);
              }elseif ($txn->user_status   == 'Vendor') {
                  User::Where('email',$pairing->email)->update([
                        'amount' => $txn->amount + $pairing->amount,
                     ]);
              }
            }else{
               $update = DB::table('transactions')->where('id',$req->id)->update([
                  'stats'=> $req->stats,
                  'reason'=> $req->reason,
                  ]);
               }
            
            if ($req->stats=='Modify') {
                $msg = "$requester->name your requested escrow transaction (Ref: $txn->ref_id) have being requested for modification in\n $req->reason.";
                $resMsg = "You have requested for Modification on the terms of service with $pairing->name  (Ref: $txn->ref_id). Reaons $req->reason";
            }
            
            if ($req->stats=='Accept') {
                  $msg = "You have successfully paired with $pairing->name (Ref: $txn->ref_id)";
                  $resMsg = "You have successfully paired with $pairing->name (Ref: $txn->ref_id)";

              }
             
          $this->sendMail($requester->email,'',$msg);
          $this->singleSMS($requester->phone, $msg);
          $transactions = DB::table('transactions')->where([['paired_id', auth('sanctum')->user()->email], ['status', 'pending']])->orwhere([['user_id', auth('sanctum')->user()->id],  ['status', 'pending']])->latest('transactions.created_at')->get();
          return response([
            'code'=>'0',
            'message'=>$resMsg,
            'header' => 'Success!',
            'data'=>[
                  'transactions'=>$transactions,
            ]
        ]);
      }
      
      public function updateTxn(Request $req)
      {
            $txnFirst = DB::table('transactions')->where('id',$req->valide)->first();
            $req->validate([
                  'status'=>'required',
                  'amount'=>'required|int',
                  'installment'=>'required',

            ]); //Validate Request
             
            $a=1;
           
            while ($a <= $req->installment) {
                  $req->validate([
                        'percent_'.$a => 'required|int',
                        'desc_'.$a => 'required'
                  ]); //Validate percents & description
            $a++; 
            }
            $total_percent=$req->percent_1+$req->percent_2+$req->percent_3;
            
            if ($total_percent > 100) {
                 abort(422, "Total Percent can not Exceed 100");
            }
           

            $req->validate([
                  'service'=>'required',
                  'desc'=>'required',
                  'date'=>'required',
                  'acc_number'=>'required|int',
                  'bname'=>'required',
                  'bhname'=>'required',
            ]); //Validate Request contiuniation

            //store Invoice Image on public folder
            if ($req->invoice == 'undefined' || $req->invoice == '') {
                 $invoice = $txnFirst->invoice;
            }else{
                  $invoice = explode('c/',$req->invoice->store('public'))[1];
            }
            
            $percent= ['percent_1'=>$req->percent_1, 'percent_2'=>$req->percent_2, 'percent_3'=>$req->percent_3, 'desc_1'=>$req->desc_1, 'desc_2'=>$req->desc_2, 'desc_3'=>$req->desc_3, 'paid_1'=>'', 'paid_2'=>'', 'paid_3'=>''];
            $percents = json_encode($percent);
            DB::table('transactions')->where('id',$req->valide)->update([
                  'user_status'=>$req->status,
                  'paired_id'=>$txnFirst->paired_id,
                  'amount'=>$req->amount,
                  'installment'=>$req->installment,
                  'invoice'=>$invoice,
                  'service'=>$req->service,
                  'desc'=>$req->desc,
                  'date'=>$req->date,
                  'acc_number'=>$req->acc_number,
                  'bname'=>$req->bname,
                  'bhname'=>$req->bhname,
                  'percent'=>$percents,
                  'stats' => 'Modified',
                  'updated_at'=>Carbon::now(),

             ]);
             $status = $req->status=='Customer'?'Vendor':'Customer';
             $message = auth('sanctum')->user()->name ."(Ref: $req->ref) have Modified the current request . You will be
             $status in this escrow transaction ($req->service)";
             
             DB::table('notifications')->insert([
                  'user_id'=>$txnFirst->paired_id,
                  'type'=>'request',
                  'message'=>$message,
                  'initiator_id'=>auth('sanctum')->user()->id,
                  'n_status'=>'unred',
                  'created_at' => Carbon::now(),
                  'updated_at' => Carbon::now(),
             ]);
             $token = '';
             $userPhone = User::where('email', $txnFirst->paired_id)->first();
             $this->singleSMS($userPhone->phone, $message);
             $this->sendMail($userPhone->email, $token, $message);
             return response([
                  'code'=>'1',
                  'message'=>'Transaction has being Modified. Pairing partner will receive a notification shortly!',
                  'header' => 'Success!',
              ]);

      }
      public function red()
      {
            DB::table('notifications')->where('user_id', auth('sanctum')->user()->email)->update([
                  'n_status'=> 'red',
            ]);
            $this->getUserInfo();
      }

      //Update Milestone
      public function updateMilestone(Request $req)
      {
            if ($req->reason["percent_$req->ind"]==null) {
               abort(422, "Installment $req->ind is required");
            }

            $db = DB::table('transactions')->where('id', $req->id)->first();

            $percent = json_decode($db->percent);
            
            $a = $req->ind;
            $total = 0;
              while ($a <= 3) {
                   $total += (int)$req->reason["percent_$a"];
                $a++;
              }
              $sub = 100 - $percent->percent_1;

              if ($req->ind == 1) {
                  if ($total > 100 || $total < 100) {
                 abort(403, "Total Installments can not be greater or lesser than 100");
               } 
               $percent->percent_1= $req->reason['percent_1'];
               $percent->percent_2= $req->reason['percent_2'];
               $percent->percent_3= $req->reason['percent_3'];
              }
              
            if ($req->ind == 2) {
              if ($total > $sub || $total < $sub) {
                  abort(403, "Total Installments can not be greater or lesser than $sub");
              }
            //   $percent->percent_1= $req->reason->percent_1;
              $percent->percent_2= $req->reason['percent_2'];
              $percent->percent_3= $req->reason['percent_3'];
            }
            
           
            $percent = json_encode($percent);
             DB::table('transactions')->where('id', $req->id)->update([
                  'percent' => $percent,
            ]);

            $msg = "MileStones Has being updated (Ref: $db->ref_id) by ".auth('sanctum')->user()->name;
            if (auth('sanctum')->user()->email == $db->paired_id) {
                $user = DB::table('users')->where('id', $db->user_id)->first();
            }elseif (auth('sanctum')->user()->id == $db->user_id) {
               $user = DB::table('users')->where('email', $db->paired_id)->first();
            }
            $token='';
            $this->singleSMS($user->phone, $msg);
           
            $this->sendMail($user->email, $token, $msg);

            return response([
                  'code'=>'0',
                  'message'=>'Milestone Update Has Being Sent For Approval!',
                  'header' => 'Success!',
              ]);
            
      }

      public function refList()
      {
         $refs = User::where('referred', auth('sanctum')->user()->refCode)->get();
         if (count($refs) > 0) {
          return response([
            'data' =>[
              'referred' => $refs
            ],
            'code'=>'0',
            'message'=>'Data was found',
            'header' => 'Success!',
          ]);
         }
         abort(503, 'Data not available yet');
      }

      //Deposit Request excution
      public function paidBtf(Request $request)
      {
           
         DB::table('deposits')->insert([
             'user_id' => auth('sanctum')->user()->id,
             'amount' => $request->amount,
             'accNo' => $request->accNo,
             'accName' => $request->holder,
             'bName' => $request->bank,
             'type' => $request->type,
             'status' => $request->status, 
             'created_at' => Carbon::now(),
             'updated_at' => Carbon::now(),
         ]); //Insert Transaction Request Place on Pending

         $token = '';

       $email = env('MAIL_FROM_ADDRESS');// Email from env file
          
           //send message to admin email
        $msg = "
        Transaction has being created by ".auth('sanctum')->user()->name.", Summary:
          Amount: $request->amount\n
          Acc. No: $request->accNo\n
          Bank Name: $request->bName\n
          Acc. Name: $request->accName\n
          Txn. Type: $request->type\n
          Status: $request->status\n
          Date: $request->created_at\n ";
         $this->sendMail($email, $token, $msg); // Email function
         User::where('id', auth('sanctum')->user()->id)->update([
            'amount' => $request->amount + auth('sanctum')->user()->amount,
        ]); //Update balance if confirmed
        
         if ($request->status=='confirmed') {
            User::where('id', auth('sanctum')->user()->id)->update([
                  'amount' => $request->amount + auth('sanctum')->user()->amount,
              ]); //Update balance if confirmed
      }
         return response([
            'code'=>'0',
            'message'=>'Transaction in process you will recieve your balance lesser than 5 munites',
            'header' => 'Processing',
          ]);
         
      }

      //confirm or reject transaction
      public function stats(Request $req)
      {
            $txn = DB::table('deposits')->where('id', $req->id)->first();
            $user = User::where('id', $txn->user_id)->first();
           if ($req->status=='declined') {
              DB::table('deposits')->where('id', $req->id)->update([
                  'status' => $req->status,
              ]); // Decline Transaction
           }else if ($req->status=='confirmed'){
            DB::table('deposits')->where('id', $req->id)->update([
                  'status' => $req->status,
              ]); //Confirm transaction

              User::where('id', $txn->user_id)->update([
                  'amount' => $txn->amount + $user->amount,
              ]); //Update balance if confirmed
           }
           

         //send message to transaction initiator
        $emsg = "
        Transaction has being $req->status. Transaction summary: \n
         
          S/N: $txn->id\n
          Amount: $txn->amount\n
          Acc. No: $txn->accNo\n
          Bank Name: $txn->bName\n
          Acc. Name: $txn->accName\n
          Status: $txn->status\n
          Date: $txn->created_at\n
          
        ";
        $msg = `
        Transaction has being $req->status\n
        Amount : $txn->amount\n
        Acc. No : $txn->accNo\n
        Acc. Name : $txn->accName\n
        Bank name : $txn->bName\n
        Status : $txn->status\n
        `;
        $token='';

         $this->singleSMS($user->phone, $msg); // SMS function
         $this->sendMail($user->email, $token, $emsg); // Email function

        return response([
            'code'=>'0',
            'message'=>"Transaction has being $req->status successfully",
            'header' => 'success',
          ]); // return response
         
      }

       //Report user
       public function report(Request $request)
       {
            $txn = DB::table('transactions')->where('id', $request->id)->first();
            if ($txn->user_id == auth('sanctum')->user()->id) {
               $user = User::where('email', $txn->paired_id)->first();
            }else{
               $user = User::where('id', $txn->user_id)->first();   
            }
          
          DB::table('reports')->insert([
              'from' => auth('sanctum')->user()->email,
              'to' => $user->email,
              'transID' => $txn->ref_id,
              'report' => $request->report,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now(),
          ]); //Insert Report 
 
          $token = '';
 
        $email = env('MAIL_FROM_ADDRESS');// Email from env file
           
            //send message to admin email
         $msg = "
         Report has being created by ".auth('sanctum')->user()->name.", Summary:
           from: ".auth('sanctum')->user()->email."\n
           to: $user->email\n
           transID: $txn->ref_id\n
           report: $request->report\n";
          $this->sendMail($email, $token, $msg); // Email function
 
          return response([
             'code'=>'0',
             'message'=>'Report has being sent to admin.',
             'header' => 'success',
           ]);
          
       }

       public function requestPayment(Request $req)
       {
            $txn = DB::table('transactions')->where('id', $req->id)->first();
             if ($txn->user_id == auth('sanctum')->user()->id) {
                 $user = User::where('email', $txn->paired_id)->first();
             }elseif ($txn->paired_id == auth('sanctum')->user()->email) {
                  $user = User::where('id', $txn->user_id)->first();
                  
            }
           if ($req->type=='request') {
            $msg = auth('sanctum')->user()->name.", is requesting for payment on (Ref: $txn->ref_id) this transaction";
            $token = '';
              $this->sendMail($user->email, $token, $msg);
              $this->singleSMS($user->phone, $msg);

              return response([
                  'code'=>'0',
                  'message'=>'Notification has being send to customer',
                  'header' => 'success',
                ]);
               
           }
           $convert = json_decode($txn->percent);
           $status='updaid';
           $pending = 'pending';

           if ($req->index == 0) {
             $convert->paid_1 = 'paid';
             $next = 1;
             if (empty($convert->percent_2)) {
                  $status = 'paid';
                  $pending = 'paid';
 
 
             }
           }elseif ($req->index == 1) {
            $convert->paid_2 = 'paid';
            $next = 2;
            if (empty($convert->percent_3)) {
                 $status = 'paid';
                 $pending = 'paid';


            }
          }else{
            $convert->paid_3 = 'paid';
            $next = 3;
            $status = 'paid';
          }
           $backtodefault = json_encode($convert);
           DB::table('transactions')->where('id', $req->id)->update([
            'next' => $next,
            'percent' => $backtodefault,
            'payment_status' => $status,
            'status'=> $pending,
           ]);

           $msg = auth('sanctum')->user()->name.", made payment on (Ref: $txn->ref_id) this transaction";
           $token = '';
             $this->sendMail($user->email, $token, $msg);
             $this->singleSMS($user->phone, $msg);

           return response([
            'code'=>'0',
            'message'=>'Payment has being made successfully',
            'header' => 'success',
          ]);
       }

       public function topUP(Request $d)
       {
           User::Where('id',auth('sanctum')->user()->id)->Update([
            'amount'=> auth('sanctum')->user()->amount + $d->amount,
           ]);

           return response([
            'code'=>'0',
            'message'=>'Topped Up Successfully',
            'header' => 'success',
           ]);
       }
}
