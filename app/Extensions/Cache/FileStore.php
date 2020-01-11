<?php
/**
 *
 * @author : 尤嘉兴
 * @version: 2020/1/3 9:28
 */

namespace App\Extensions\Cache;

class FileStore extends \Illuminate\Cache\FileStore
{
    protected function expiration($seconds)
    {
        $expiration = parent::expiration($seconds);
        return $expiration === 9999999999 ? 2147483600 : $expiration;
    }
}
