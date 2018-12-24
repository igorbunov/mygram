<?php
/**
 * Created by PhpStorm.
 * User: pata
 * Date: 24.12.18
 * Time: 23:06
 */

namespace App\Http\Controllers\MyInstagram;

use InstagramAPI\Instagram;

class ExtendedInstagram extends Instagram {
    public function changeUser( $username, $password ) {
        $this->_setUser( $username, $password );
    }
}