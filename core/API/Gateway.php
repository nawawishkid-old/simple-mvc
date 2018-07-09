<?php

namespace Core\API;

use Core\Request;
use Core\Response;

class Gateway
{
    protected $addedResourceIdentifier = [];

    public function activate(Request $request, Response $response)
    {
        if (!$this->protocalExists($request->protocal)) {
            throw new \Exception("Unknown request protocal: $request->protocal", 1);
            
        }
    }

    public function addProtocal(string $protocalName, array $protocalStructure)
    {
        $this->$addedResourceIdentifier[$protocalName] = $protocalStructure;
    }

    public function addResourceIdentifier(
        string $protocalName, 
        callable $addingMethod,
        callable $resourceCallback
    )
    {
        if (empty($this->$addedResourceIdentifier[$protocalName])) {
            throw new \Exception("Unknown protocal, $protocalName", 1);
            
        }

        $protocal = \call_user_func_array($addingMethod);

        if (empty($protocal)) {
            throw new \Exception("Protocal method cannot be empty. On $protocalName protocal.", 1);
            
        }

        $this->$addedResourceIdentifier[$protocalName] = $protocal;
    }

    private function protocalExists(string $protocalName)
    {
        return empty($this->$addedResourceIdentifier[$protocalName]);
    }
}

// $gateway = new Gateway();
// $gateway->addProtocal('http', [
//     'get' => [],
//     'post' => [],
//     'delete' => [],
// ]);
// $gateway->addResourceIdentifier(
//     'http', 
//     function ($protocal, $resourceCallback) {
//         $protocal['get']['user/1'] = $resourceCallback;
//         return $protocal;
//     },
//     function ($request, $response) {
//         return $response->json($request->data, 200);
//     }
// );