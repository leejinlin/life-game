<?php

trait TestTrait
{
    private function parseCriteria()
    {
        $datas = func_get_args();
        $methods = [];
        $vars = [];
        foreach ($datas as $data) {
            if (!is_array($data)) continue;
            foreach ($data as $k=>$v) {
                if (is_object($v) && ($v instanceof \Closure)) {
                    $methods[$k] = $v;
                } else {
                    $vars[$k] = $v;
                }
            }
        }

        return [$methods, $vars];
    }

    // 某些情况，仅仅需要对特定的method进行mock，其他的直接clone原有的即可
    private function extend($class, array $iCriteria=[])
    {
        $this->unextend($class);

        \Gini\IoC::bind($class, function ($criteria=null) use ($class, $iCriteria) {

            list($methods, $vars) = $this->parseCriteria($iCriteria, $criteria);

            if (empty(array_keys($methods))) {
                $methods = [
                    '_'=> function () {}
                ];
            }

            $object = $this->getMockBuilder($class)
                        ->setMethods(array_keys($methods))
                        ->disableOriginalConstructor()
                        ->getMock();

            foreach ($methods as $k=>$v) {
                $object->expects($this->any())
                    ->method($k)
                    ->will($this->returnCallback(function () use ($object, $v) {
                        $args = func_get_args();
                        array_push($args, $object);

                        return call_user_func_array($v, $args);
                    }));
            }

            foreach ($vars as $k=>$v) {
                $object->$k = $v;
            }

            if (is_numeric($criteria)) {
                $object->id = $criteria;
            }

            return $object;
        });
    }

    private function mock($class, array $iCriteria=[])
    {
        $this->unmock($class);

        \Gini\IoC::bind($class, function ($criteria=null) use ($class, $iCriteria) {

            list($methods, $vars) = $this->parseCriteria($iCriteria, $criteria);

            $object = $this->getMockBuilder($class)
                        ->disableOriginalConstructor()
                        ->getMock();

            foreach ($methods as $k=>$v) {
                $object->expects($this->any())
                    ->method($k)
                    ->will($this->returnCallback(function () use ($object, $v) {
                        $args = func_get_args();
                        array_push($args, $object);

                        return call_user_func_array($v, $args);
                    }));
            }

            foreach ($vars as $k=>$v) {
                $object->$k = $v;
            }

            if (is_numeric($criteria)) {
                $object->id = $criteria;
            }

            return $object;
        });
    }

    private function unextend($class)
    {
        \Gini\IoC::clear($class);
    }

    private function unmock($class)
    {
        \Gini\IoC::clear($class);
    }

    /**
        * @brief 模拟对指定path的请求，并将数据返回
        *
        * @param $path
        * @param $env: ['get'=>$_GET, 'post'=>$_POST, 'files'=>$_FILES, 'route'=>self::$route]
        *
        * @return
     */
    private function requestHTML($path, array $env=[])
    {
        \Gini\IoC::bind('\Gini\View', function ($path, $vars) use (&$vars_result) {
            $vars_result['path'] = $path;
            $vars_result['vars'] = $vars;
        });
        $vars_result = [];
        try {
            \Gini\CGI::request($path, $env)->execute();
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $bool = !!preg_match('/^Cannot modify header information/', $msg);
            if ($bool) {
                $vars_result['redirect'] = true;
            }
        }
        \Gini\IoC::clear('\Gini\View');

        return $vars_result;
    }

    private function requestJSON($path, array $env=[])
    {
        return \Gini\CGI::request($path, $env)->execute()->content();
    }

    private function login($user)
    {
        $me = a('user', $user);
        _G('ME', $me);
    }

    private function logout()
    {
        $me = a('user', 0);
        _G('ME', $me);
    }

    private function rpcCall($method, $params)
    {
        $data = [
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $params,
            'id' => uniqid(),
        ];
        $response = \Gini\API::dispatch($data);
        return $response['result'];
    }

}
$gini_dirs = [
    isset($_SERVER['GINI_SYS_PATH']) ? $_SERVER['GINI_SYS_PATH'] . '/lib' : __DIR__ . '/../../gini/lib',
    (getenv("COMPOSER_HOME") ?: getenv("HOME") . '/.composer') . '/vendor/iamfat/gini/lib',
    '/usr/share/local/gini/lib',
];

foreach ($gini_dirs as $dir) {
    $file = $dir.'/phpunit.php';
    if (file_exists($file)) {
        require_once $file;

        return;
    }
}

die("missing Gini PHPUnit Components!\n");
