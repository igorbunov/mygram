<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProxyIps extends Model
{
    public static function setAccountId(ProxyIps $proxyIP, int $accountId)
    {
        if ($accountId == 0) {
            throw new \Exception('cant set account id in proxy, he is 0');
        }

        $res = self::find($proxyIP->id);

        if (is_null($res)) {
            throw new \Exception('cant find proxy ip row by id: ' . $proxyIP->id);
        }

        $res->account_id = $accountId;
        $res->save();
    }
    public static function getFreeIp(int $accountId = 0)
    {
        return self::where([
            'account_id' => $accountId
        ])->first();
    }
}
