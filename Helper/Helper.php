<?php

/**
 * Created by PhpStorm.
 * User: hautruong
 * Date: 7/28/17
 * Time: 10:51 AM
 */

namespace conghau\Bundle\ApiResource\Helper;

/**
 * Class TCHHelper
 * @package conghau\ApiResource\Helper
 */
class Helper
{
    /**
     * @param string $input
     *
     * @return string
     */
    public static function fromCamelCase($input)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }

        return implode('_', $ret);
    }

    /**
     * @param $string
     *
     * @return mixed
     */
    public static function clean($string)
    {
        return preg_replace('/[\/()^:]/', '', trim($string)); // Removes special chars.
    }

    /**
     * @param string $string
     *
     * @return mixed|string
     */
    public static function convertWithDash(string $string)
    {
        //Clean up multiple dashes or whitespaces
        $string = preg_replace("/[\s-]+/", " ", $string);
        //Convert whitespaces and underscore to dash
        $string = preg_replace("/[\s_]/", "-", $string);

        return $string;
    }
}