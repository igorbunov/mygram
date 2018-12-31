<?php
/**
 * Created by PhpStorm.
 * User: pata
 * Date: 24.12.18
 * Time: 23:06
 */

namespace App\Http\Controllers\MyInstagram;

use Illuminate\Support\Facades\Log;
use InstagramAPI\Instagram;
use InstagramAPI\Response;

class ExtendedInstagram extends Instagram {
    public function afterChallengeRelogin($newPk)
    {
        $this->isMaybeLoggedIn = true;
        $this->account_id = $newPk;
        $this->settings->set('account_id', $this->account_id);
        $this->settings->set('last_login', time());

        Log::debug('afterChallengeRelogin ' . $newPk);
    }
}