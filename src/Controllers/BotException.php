<?php

namespace App\Http\Controllers\Service\Telegram;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class BotException //extends Controller
{
    public function errorMessage() {
        return 'Error message';
    }
}
