<?php
/**
 * phpunit tests/CLI/Run.php
 */
namespace Gini\PHPUnit\CGI;

require_once __DIR__ . '/../gini.php';

class Index extends \Gini\PHPUnit\CGI
{
    use \TestTrait;

    public function setup()
    {
    }

    public function tearDown()
    {
    }

	protected static function getMethod($name) {
		$class = new \ReflectionClass('\Gini\Controller\CLI\Run');
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method;
	}

    public function testSetMap()
    {
        $foo = self::getMethod('setMap');
        $obj = new \Gini\Controller\CLI\Run();
        $map = [];
        $result = $foo->invokeArgs($obj, [0, 0]);
        $this->assertEquals($map, $result);

        $map = [];
        $result = $foo->invokeArgs($obj, [0, 1]);
        $this->assertEquals($map, $result);

        $map = [[0]];
        $result = $foo->invokeArgs($obj, [1, 1]);
        $this->assertEquals($map, $result);

        $map = [[0,0],[0,0]];
        $result = $foo->invokeArgs($obj, [2, 2]);
        $this->assertEquals($map, $result);
    }

    public function testSetCell()
    {
        $foo = self::getMethod('setCell');
        $obj = new \Gini\Controller\CLI\Run();

        // 设定的细胞点超出了map
        $vars = [
        	[[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0]],
        	[[6,10]]
        ];
        $result = $foo->invokeArgs($obj, $vars);
        $ret = false;
        $this->assertEquals($ret, $result);


        // 设定的细胞点坐标不符合规范
        $vars = [
        	[[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0]],
        	[[1,2,3]]
        ];
        $result = $foo->invokeArgs($obj, $vars);
        $ret = false;
        $this->assertEquals($ret, $result);

        // 设定的细胞点坐标符合规范
        $vars = [
        	[[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0]],
        	[[1,2]]
        ];
        $result = $foo->invokeArgs($obj, $vars);
        $ret = [[0,0,0,0],[0,0,1,0],[0,0,0,0],[0,0,0,0]];
        $this->assertEquals($ret, $result);

    }

    public function testShow()
    {
        $foo = self::getMethod('show');
        $obj = new \Gini\Controller\CLI\Run();

        //格式不符合规范
        $vars = [
        	['a'],
        ];
        $result = $foo->invokeArgs($obj, $vars);
		$ret = false;
        $this->assertEquals($ret, $result);


        $vars = [
        	[[0,0,],[0,1]],
        ];
        $result = $foo->invokeArgs($obj, $vars);
		$ret =
		"|  |
| o|
****
";
        $this->assertEquals($ret, $result);

    }

    public function testCheckAround()
    {
        $foo = self::getMethod('checkAround');
        $obj = new \Gini\Controller\CLI\Run();

        // 监测点不在地图内
		$vars = [
			5,
			7,
			[[0,0,0], [1,1,1],[0,0,0]]
		];
		$count = $foo->invokeArgs($obj, $vars);
		$num = 0;
		$this->assertEquals($num, $count);

		// 监测点所在地图一个活的都没有
		$vars = [
			1,
			2,
			[[0,0,0], [0,0,0],[0,0,0]]
		];
		$count = $foo->invokeArgs($obj, $vars);
		$num = 0;
		$this->assertEquals($num, $count);

		// 监测点靠侧边所在地图只有监测点自己这个活细胞
		$vars = [
			1,
			2,
			[[0,0,0], [0,0,1],[0,0,0]]
		];
		$count = $foo->invokeArgs($obj, $vars);
		$num = 0;
		$this->assertEquals($num, $count);

		// 监测点靠侧边所在地图只有监测点周围有一个活细胞
		$vars = [
			1,
			2,
			[[0,0,0], [0,1,0],[0,0,0]]
		];
		$count = $foo->invokeArgs($obj, $vars);
		$num = 1;
		$this->assertEquals($num, $count);

		// 监测点靠侧边所在地图只有监测点周围有2个活细胞
		$vars = [
			1,
			2,
			[[0,0,0], [0,1,0],[0,0,1]]
		];
		$count = $foo->invokeArgs($obj, $vars);
		$num = 2;
		$this->assertEquals($num, $count);

		// 监测点靠顶边所在地图只有监测点自己这个活细胞
		$vars = [
			0,
			1,
			[[0,1,0], [0,0,0],[0,0,0]]
		];
		$count = $foo->invokeArgs($obj, $vars);
		$num = 0;
		$this->assertEquals($num, $count);

		// 监测点靠顶边所在地图只有监测点周围有一个活细胞
		$vars = [
			0,
			1,
			[[1,1,0], [0,0,0],[0,0,0]]
		];
		$count = $foo->invokeArgs($obj, $vars);
		$num = 1;
		$this->assertEquals($num, $count);

		// 监测点靠顶边所在地图只有监测点周围有2个活细胞
		$vars = [
			0,
			1,
			[[1,1,1], [0,0,0],[0,0,1]]
		];
		$count = $foo->invokeArgs($obj, $vars);
		$num = 2;
		$this->assertEquals($num, $count);

		// 直角点检测
		$vars = [
			0,
			2,
			[[0,1,1], [0,0,1],[0,0,1]]
		];
		$count = $foo->invokeArgs($obj, $vars);
		$num = 2;
		$this->assertEquals($num, $count);

    }


    public function testBreed()
    {
        $foo = self::getMethod('Breed');
        $obj = new \Gini\Controller\CLI\Run();

        // 空地图
		$vars = [
			[[0,0,0], [0,0,0],[0,0,0]]
		];
		$result = $foo->invokeArgs($obj, $vars);
		$map = [[0,0,0], [0,0,0],[0,0,0]];
		$this->assertEquals($map, $result);

		//在其它情况下，该细胞为死
		$vars = [
			[[0,0,0], [1,1,0],[0,0,0]]
		];
		$result = $foo->invokeArgs($obj, $vars);
		$map = [[0,0,0], [2,2,0],[0,0,0]];
		$this->assertEquals($map, $result);

		// 如果一个细胞周围有3个细胞为生,
		// 如果一个细胞周围有2个细胞为生
		$vars = [
			[[0,0,0], [1,1,1],[0,0,0]]
		];
		$result = $foo->invokeArgs($obj, $vars);
		$map = [[0,1,0], [2,1,2],[0,1,0]];
		$this->assertEquals($map, $result);

    }
}