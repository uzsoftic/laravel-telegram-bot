<?php

namespace App\Http\Controllers\Service\Telegram;

use App\Http\Controllers\Controller;

class BotFunctions extends Controller{

    public function clearNumber($number){
        $clean = preg_replace('/\D/', '', $number);
        if(strlen($clean) == 9){
            $generated = '998'.$clean;
        }elseif(strlen($clean) == 12){
            $generated = $clean;
        }else{
            $generated = $clean;
        }
        return $generated;
    }

    public function clearVerifyCode($number){
        return preg_replace('/\D/', '', $number);
    }

    public function formatPrice($amount, $currency = 'сум'){
        return number_format($amount)." ".$currency;
    }

    public function clearName($name){
        $clean = htmlspecialchars(strip_tags($name));
        if(strlen($clean) > 8 && strlen($clean) < 64){
            $generated = $clean;
        }else{
            $generated = NULL;
        }
        return $generated;
    }

    public function removeEmoji($string){
        // Match Emoticons
        $regex_emoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $clear_string = preg_replace($regex_emoticons, '', $string);
        // Match Miscellaneous Symbols and Pictographs
        $regex_symbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clear_string = preg_replace($regex_symbols, '', $clear_string);
        // Match Transport And Map Symbols
        $regex_transport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clear_string = preg_replace($regex_transport, '', $clear_string);
        // Match Miscellaneous Symbols
        $regex_misc = '/[\x{2600}-\x{26FF}]/u';
        $clear_string = preg_replace($regex_misc, '', $clear_string);
        // Match Dingbats
        $regex_dingbats = '/[\x{2700}-\x{27BF}]/u';
        $clear_string = preg_replace($regex_dingbats, '', $clear_string);

        return $clear_string;
    }

    public function translate($string, $language){
        $dictionary = openJSONFile($language);

        if(isset($dictionary[$string])){
            if(empty($dictionary[$string])){
                return $string;
            }else{
                return $dictionary[$string];
            }
        }else{
            return $string;
        }
    }

    public function numHash($method, $number){
        if($method == 'enc')
            return hexdec(($number + 149) * 2);
        elseif($method == 'dec')
            return (dechex($number) / 2) - 149;
    }

}
