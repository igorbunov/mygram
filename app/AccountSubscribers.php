<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class AccountSubscribers extends Model
{
    public static function addUnique($data)
    {
        // TODO: решить как доставать новых подпищиков
        
        try {
            $subs = new AccountSubscribers();

            $subs->owner_account_id = $data['owner_account_id'];
            $subs->username =  $data['username'];
            $subs->pk = $data['pk'];
            $subs->json = $data['json'];

            $subs->save();
        } catch (\Exception $err) {
//            Log::error('dublicate key: '.$data['pk']);
        }
    }

    public static function getNewSubscribers(int $accountId)
    {

    }
}
