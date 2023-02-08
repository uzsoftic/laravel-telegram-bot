<?php

namespace App\Http\Controllers\Service\Telegram;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\OrderController;
use Carbon\Carbon;
use App\Models\TelegramUser;
use App\Models\Product;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

//use App\Http\Controllers\Telegram\Bot;
//use App\Http\Controllers\Service\Telegram\BotCommands;
use App\Http\Controllers\Service\Telegram\BotFunctions;

use App\Http\Controllers\Service\Telegram\Ecommerce\EcommerceRender as Render;

class BotCommands extends Controller{

    //public $bot;
    public $render;
    public $functions;

    public function __construct(){
        //$this->bot = new Bot('ecommerce');
        $this->functions = new BotFunctions;
        $this->render = new Render;
    }

    public function test(){
        //return $this->setWebhook('getWebhookInfo');
        //dump('blabla');
    }

    public function setWebhook($method, $token, $params = false){
        if (in_array($method, array('setWebhook', 'getWebhookInfo', 'deleteWebhook'))) {

            $api = config('telegram.call_api').$token."/".$method ?? false;

            if($params && $api){
                return $this->setRequest('GET', $api, [ 'url' => $params ]);
            }else{
                return $this->setRequest('GET', $api);
            }
        }else{
            return 'Wrong method: '.$method;
        }
    }

    public function setRequest($method, $api, $params = []){
        $client = new Client();
        $request = $client->request($method, $api, ['form_params'=>$params]);
        if ($request->getStatusCode() == 200){  // is OK
            echo $request->getBody()->getContents();
        }else{
            echo 'error';
        }
    }

    public function setLanguage($chat_id, $act, $code, $member = NULL){
        if($act == 'set' && !$this->checkUserExists($chat_id)){
            $user = new TelegramUser;
            $user->chat_id = $chat_id;
            $user->language = $code;
            $user->recent = 'required_name';
/*            if($member != NULL){
                $account = User::findOrFail($member);

                $user->user_id = $member;
                $user->name = $account->name;
                $user->region = $account->region;
                $user->address = $account->address;
                $user->email = $account->email;
                $user->recent = 'required_phone';
            }*/
            if($user->save()){
                return 1;
            }
        }else{
            $user = TelegramUser::where('chat_id', $chat_id)->firstOrFail();
            $user->language = $code;
            if($user->save()){
                return 1;
            }
        }
        return 0;
    }

    public function checkUserStatus($chat_id){
        //$status = TelegramUser::where('chat_id', $chat_id)->exists();
        $status = $this->checkUserExists($chat_id);
        if($status)
            $status = TelegramUser::where('chat_id', $chat_id)->first()->active ? true : false;

        return $status;
    }

    public function connect($type){
        if($type == 'emptyWebhook')
            return $this->setWebhook('setWebhook');
        if($type == 'setWebhook')
            return $this->setWebhook('setWebhook', config('telegram.bot.ecommerce.callback'));
        if($type == 'getWebhookInfo')
            return $this->setWebhook('getWebhookInfo');
        if($type == 'deleteWebhook')
            return $this->setWebhook('deleteWebhook');
    }

    public function admin(){
        return view('telegram.ecommerce.index');
        //return 'erere';
    }

    public function editProfile($type, $chat_id, $param, $value = null){
        $telegram_user = TelegramUser::where('chat_id', '=', $chat_id)->first();

        if($type == 'new_value_text'){

            $info = json_decode($telegram_user->information, true);
            if(empty($info)){ $info = array(); }

            switch ($param) {
                case 'phone':
                    if(!$this->functions->clearNumber($value)){
                        return false;
                    }else{
                        $info[$param] = $this->functions->clearNumber($value);
                    }
                break;
                case 'number':
                    if(!$this->functions->clearNumber($value)){
                        return false;
                    }else{
                        $info[$param] = $this->functions->clearNumber($value);
                    }
                break;
                case 'name':
                    if(preg_match('~^[a-z0-9&./?]+$~i', $value)){
                        return false;
                    }else{
                        $info[$param] = $value;
                    }
                break;
                case 'address':
                    if(mb_strlen($value) < 4){
                        return false;
                    }else{
                        $info[$param] = $value;
                    }
                break;
                case 'email':
                    if(mb_strlen($value) < 4){
                        return false;
                    }else{
                        $info[$param] = $value;
                    }
                break;
                case 'comment':
                    if(mb_strlen($value) > 512){
                        return false;
                    }else{
                        $info[$param] = $value;
                    }
                break;
                default:
                    $info[$param] = $value;
                break;
            }

            $telegram_user->information = json_encode($info);
            //$telegram_user->recent = 'empty';

            if($telegram_user->save()){
                return 1;
            }

            return 0;
        }elseif($type == 'callback'){
            $telegram_user->recent = 'edit_'.$param;
            $telegram_user->save() ? true : false;
        }else{
            return false;
        }
    }

    public function addProductToCart($chat_id, $product_id, $qty){
        $user = TelegramUser::where('chat_id', '=', $chat_id)->first();

        if(!empty($user->cart)){
            $user_cart = json_decode($user->cart);
        }
        else{
            $user_cart = array();
        }

        $user_cart[] = array(
            'id' => $product_id,
            'count' => $qty,
        );

        $user->cart = json_encode($user_cart);

        if($user->save()){
            return true;
        }
        else{
            return false;
        }

    }

    public function clearCart($chat_id){
        $user = TelegramUser::where('chat_id', $chat_id)->first();

        $user->cart = NULL;

        if($user->save()){
            return true;
        }
        else{
            return false;
        }
    }

    public function createOrder($chat_id, $message_id){
        $user = TelegramUser::where('chat_id', '=', $chat_id)->first();
        $info = json_decode($user->information, true);

        $order = new TelegramOrder();
        $order->user_id = $user->user_id ?? NULL;
        $order->chat_id = $chat_id;
        $order->order_details = $user->cart;
        $order->address = $info['address'] ?? NULL;
        $order->note = NULL;
        $order->delivery_status = 0;
        $order->payment_status = 0;
        $order->status = 0;
        $order->save();

        $this->sendFullMessage($chat_id, $message_id, $this->render->showOrder($chat_id), $this->render->makeInline($this->render->keyOrder()));

    }

    public function storeUserName($chat_id, $name){
        $user = TelegramUser::where('chat_id', $chat_id)->first();
        $user->name = $name;
        $user->recent = 'required_phone';
        $user->save();
    }

    public function storePhoneNumber($chat_id, $number){
                //return 0;
        $generated_code = rand(100000, 999999);
        if(strlen($number) == 12){
            $response = sendSMS($number, 'OPENSHOP.UZ Ваш код для подтверждения: '.$generated_code);

            if($response != false){
                //sendTelegram('me', 'tgtg '.json_decode($response));
                //$status = json_decode($response)->status;
                $status = 'success';
            }else{
                $status = 'error';
            }

            if($status != 'error'){
                sendTelegram('me', (string) $number);
                $user = TelegramUser::where('chat_id', $chat_id)->first();
                $user->recent = 'confirm_phone';
                $user->number = (int)$number;
                $user->verify_code = $generated_code;
                $user->verify_sended = now()->timestamp;
                if($user->save()){
                    return 1;
                }else{
                    return 0;
                }
            }
        }
        //sendTelegram('me', json_encode($response));
        return 0;

    }

    public function checkUserDetailsForOrder($chat_id){
        $phone = $this->render->getUserInfo('phone', $chat_id, 'empty', 0);
        $address = $this->render->getUserInfo('address', $chat_id, 'empty', 0);
        $comment = $this->render->getUserInfo('comment', $chat_id, 'empty', 0);

        if($phone == 'empty'){
            $this->setRecent($chat_id, 'checkout');
            $this->setBack($chat_id, 'checkout');
            return 'phone';
        }
        if($address == 'empty'){
            $this->setRecent($chat_id, 'checkout');
            $this->setBack($chat_id, 'checkout');
            return 'address';
        }

        return $this->storeOrder($chat_id);
    }

    public function storeUserDetails($chat_id, $details){
        $user = TelegramUser::where('chat_id', $chat_id)->firstOrFail();
        if($user->details != json_encode($details)){
            $user->username = $details->username ?? NULL;
            $user->details = json_encode($details);
            if($user->save()){
                return 1;
            }
        }
        return 0;
    }

    public function storeOrder($chat_id){
        $user = TelegramUser::where('chat_id', $chat_id)->first();

        $cart = $user?->cart;
        if(isset($cart) && !empty($cart) && !empty($user?->information ?? NULL)){
            $info = json_decode($user?->information ?? []);
                $info->guest_id = $chat_id;
                //$info->direction = 'telegram';
                //$info->payment_type = 'cash_on_delivery';
                //$info->payment_status = 'unpaid';
                $info->shipping_address = [
                    'name' => $info->name = $info->name ?? $user->name ?? 'Guest',
                    'address' => $info->address, //$chat_id
                    'country' => 'Uzbekistan',
                    'city' => $info->city ?? 'Uz',
                    'phone' => $info->phone ?? $user->number,
                    'checkout_type' => $info->city ?? 'guest',
                    'email' => $info->email ?? 'telegram_order@openshop.uz',
                    'country' => null,
                    'postal_code' => null,
                ];

            $cart = [];
            foreach (json_decode($user->cart) as $key => $cartItem){
                $cart[$key] = [
                    "id" => $cartItem->id,
                    "quantity" => $cartItem->count,
                    "count" => $cartItem->count,
                    "price" => home_discounted_base_price($cartItem->id, 1),
                    "tax" => 0,
                    "shipping" => 0,
                    "shipping_type" => "home_delivery",
                ];
            }

            $order = new OrderController;
            $result = $order->storeTelegramOrder($info, $cart);

            if($result > 0){
                return $result;
            }
        }

        return 0;
    }

    public function resendVerifyCode($chat_id){
        $user = TelegramUser::where('chat_id', $chat_id)->first();
        $sms_access = Carbon::now()->timestamp > Carbon::createFromTimestamp($user->verify_sended)->addSeconds(120)->timestamp ? 1 : 0;

        if($sms_access){
            $generated_code = rand(100000, 999999);
            $user->recent = 'confirm_phone';
            $user->verify_code = $generated_code;
            $user->verify_sended = now()->timestamp;
            $user->save();

            sendSMS($user->number, 'OPENSHOP.UZ Ваш код для подтверждения: '.$generated_code);
            return 1;
        }
        return 0;
    }

    public function confirmVerifyCode($chat_id, $number){
        $user = TelegramUser::where('chat_id', $chat_id)->first();
        if($number){
            $user->active = 1;
            $user->recent = 'empty';
            $user->save();
        }
    }

    public function checkUserExists($chat_id){
        return TelegramUser::where('chat_id', $chat_id)->exists();
    }

    public function getUserLanguage($chat_id){
        if($this->checkUserExists($chat_id)){
            return TelegramUser::where('chat_id', $chat_id)->firstOrFail()->language;
        }
        return 'ru';
    }

    public function checkUserRecent($chat_id){
        if($this->checkUserExists($chat_id)){
            $auth_account = TelegramUser::where('chat_id', $chat_id)->first() ?? NULL;
            return isset($auth_account->recent) ? $auth_account->recent : NULL;
        }else{
            return false;
        }
    }

    public function checkUserBack($chat_id){
        if($this->checkUserExists($chat_id)){
            $auth_account = TelegramUser::where('chat_id', $chat_id)->first() ?? NULL;
            return isset($auth_account->back) ? $auth_account->back : NULL;
        }else{
            return false;
        }
    }

    public function removeUser($chat_id){
        $result = TelegramUser::where('chat_id', $chat_id)->delete();
        return $result;
    }

    public function restartUser($chat_id){
        $result = TelegramUser::where('chat_id', $chat_id)->update(['recent' => 'empty', 'back' => 'empty', 'cart' => NULL]);
        return $result;
    }

    public function restartUserRecent($chat_id){
        $result = TelegramUser::where('chat_id', $chat_id)->update(['recent' => 'empty', 'back' => 'empty']);
        return $result;
    }

    public function removeNotCompletedUser($chat_id){
        $result = TelegramUser::where('chat_id', $chat_id)->delete();
        return $result;
    }

    public function searchByProducts($query){
        $query = $this->functions->removeEmoji(strip_tags($query));
        $products = filterProducts(Product::where('name', 'LIKE', '%'.$query.'%'), 0)->orderBy('num_of_view', 'DESC')->limit(30)->get();
        return $products;
    }

    public function secondTest(){
        dump('ggwp');
    }


    public function setRecent($chat_id, $text = 'empty'){
        return TelegramUser::where('chat_id', $chat_id)->update(['recent' => $text]);
    }

    public function setBack($chat_id, $text = 'empty'){
        return TelegramUser::where('chat_id', $chat_id)->update(['back' => $text]);
    }

}
