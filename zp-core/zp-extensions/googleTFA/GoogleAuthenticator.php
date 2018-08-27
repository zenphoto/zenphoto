<?php

namespace Dolondro\GoogleAuthenticator;

use Base32\Base32;
use Psr\Cache\CacheItemPoolInterface;

class GoogleAuthenticator
{
    // According to the spec, this could be something other than 6. But again, apparently Google Authenticator ignores
    // that part of the spec...
    protected $codeLength = 6;

    /**
     * @var CacheItemPoolInterface|null
     */
    protected $cachePool = null;

    /**
     * @param CacheItemPoolInterface $cacheItemPoolInterface
     */
    public function setCache(CacheItemPoolInterface $cacheItemPoolInterface)
    {
        $this->cachePool = $cacheItemPoolInterface;
    }

    /**
     * @param $secret
     * @param $code
     *
     * @return bool
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function authenticate($secret, $code)
    {
        $correct = false;
        for ($i = -1; $i <= 1; $i++) {
            if ($this->calculateCode($secret) == $code) {
                $correct = true;

                break;
            }
        }

        // If they're not using a cache to prevent people being able to use the same code twice or if they were wrong anyway...
        if (!isset($this->cachePool) || !$correct) {
            return $correct;
        }

        // If we're here then we must be using a cache, and we must be right

        // We generate the key as securely as possible, then salt it using something that will always be replicable.
        // We're doing this hashing for de-duplication (aka, we want to know if it exists), but as we're also possibly
        // securing the secret somewhere, we want to try and have as secure as possible
        //
        // Annoyingly, crypt looks like it can return characters outside of the range of acceptable keys, so we're just
        // md5'ing again to make the characters acceptable :P
        // There definitely will be a better way of doing this, but this is a quick bugfix
        //
        // If someone has any better suggestions on how to achieve this, please send in a PR! :P
        $key = md5(crypt($secret."|".$code, md5($code)));

        // If it existed, then we want this function to return false
        if ($this->cachePool->hasItem($key)) {
            return false;
        }

        // If it didn't, then we want this function to add it to the cache
        // In PSR-6 getItem will always contain an CacheItemInterface and that seems to be the only way to add stuff
        // to the cachePool
        $item = $this->cachePool->getItem($key);
        // It's a quick expiry thing, 30 seconds is more than long enough
        $item->expiresAfter(new \DateInterval("PT30S"));
        // We don't care about the value at all, it's just something that's needed to use the caching interface
        $item->set(true);
        $this->cachePool->save($item);

        return true;
    }

    protected function getTimeSlice($offset = 0)
    {
        return floor(time() / 30) + ($offset * 30);
    }

    /**
     * @param $secret
     * @param null $timeSlice
     *
     * @return string
     */
    public function calculateCode($secret, $timeSlice = null)
    {
        // If we haven't been fed a timeSlice, then get one.
        // It looks a bit unclean doing it like this, but it allows us to write testable code
        $timeSlice = $timeSlice ? $timeSlice : $this->getTimeSlice();

        // Packs the timeslice as a "unsigned long" (always 32 bit, big endian byte order)
        $timeSlice = pack("N", $timeSlice);

        // Then pad it with the null terminator
        $timeSlice = str_pad($timeSlice, 8, chr(0), STR_PAD_LEFT);

        // Hash it with SHA1. The spec does offer the idea of other algorithms, but notes that the authenticator is currently
        // ignoring it...
        $hash = hash_hmac("SHA1", $timeSlice, Base32::decode($secret), true);

        // Last 4 bits are an offset apparently
        $offset = ord(substr($hash, -1)) & 0x0F;

        // Grab the last 4 bytes
        $result = substr($hash, $offset, 4);

        // Unpack it again
        $value = unpack('N', $result)[1];

        // Only 32 bits
        $value = $value & 0x7FFFFFFF;

        // Modulo down to the right number of digits
        $modulo = pow(10, $this->codeLength);

        // Finally, pad out the string with 0s
        return str_pad($value % $modulo, $this->codeLength, '0', STR_PAD_LEFT);
    }
}
