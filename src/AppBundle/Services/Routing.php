<?php

namespace AppBundle\Services;

use AppBundle\Base\BaseService;

class Routing extends BaseService
{
    public function getCurrentRoute()
    {
        $request = $this->get('request_stack')->getMasterRequest();

        if ($request->get('_route')[0] == '_') {
            return;
        }

        return [
            'name'   => $request->get('_route'),
            'params' => array_merge($request->get('_route_params') ?: [], $request->query->all()),
        ];
    }
}
