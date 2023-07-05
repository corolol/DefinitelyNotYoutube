<?php
namespace AppBundle\Utility;

function randomString(int $length, $characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz"): string
{
    $charLength = strlen($characters);
    $result = '';

    for ($i = 0; $i < $length; $i++) {
        $result .= $characters[random_int(0, $charLength - 1)];
    }

    return $result;
}