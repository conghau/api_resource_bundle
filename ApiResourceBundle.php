<?php

/**
 * Created by PhpStorm.
 * User: hautruong
 * Date: 7/28/17
 * Time: 1:50 PM
 */

namespace conghau\Bundle\ApiResource;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use conghau\Bundle\ApiResource\DependencyInjection\ApiResourceExtension;

/**
 * Class ApiResourceBundle
 * @package conghau\ApiResource
 */
class ApiResourceBundle extends Bundle
{
    /**
     * @return ApiResourceExtension
     */
    public function getContainerExtension()
    {
        return new ApiResourceExtension();
    }
}
