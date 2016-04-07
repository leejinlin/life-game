<?php

namespace Gini\Controller\CLI;

class Run extends \Gini\Controller\CLI
{
	public function actionLifeGame()
	{
		$width  = 20;
		$height = 10;
		$criteria = [[1,2],[1,3],[1,4]];
		$dealine = false;
		$deadline = 5;
        // 创建地图
        $map = self::setMap($height, $width);
        $map = self::setCell($map, $criteria);
        echo self::show($map);
        while ($deadline--) {
            // 一代一代繁殖
            $map = self::breed($map);
            echo self::show($map);
        }
	}

	private function show($map) {
		if (!is_array($map)) return false;
		$str = '';
		foreach ($map as $rows) {
			if (!is_array($rows)) return false;
			$str .= '|';
			$n = 2;
			foreach ($rows as $cell) {
				$n++;
				if (!$cell) {
					$str .= ' ';
				}
				elseif ($cell == 1) {
					$str .= 'o';
				}
				else {
					$str .= 'x';
				}
			}
			$str .= "|\n";
		}
		while ($n--) {
			$str .= "*";
		}
		$str .= "\n";
		return $str;
	}

    public function setMap($width, $height)
    {
        $arr = [];
        for ($i=0; $i < (int)$width; $i++) {
            for ($n=0; $n < $height; $n++) {
                $arr[$i][$n] = 0;
            }
        }
        return $arr;
    }

    private function setCell($map, $criteria)
    {
    	if (!is_array($map)) return false;
        foreach ($criteria as $xy) {
        	if (count($xy) > 2 || !isset($xy[0]) || !isset($xy[1])) return false;
            $x = $xy[0];
            $y = $xy[1];
            if (!isset($map[$x][$y])) return false;
            $map[$x][$y] = 1;
        }

        return $map;
    }

    public function checkAround($x, $y, $map) {
        $x1 = (int)$x + 1;
        $x2 = (int)$x - 1;
        $y1 = (int)$y + 1;
        $y2 = (int)$y - 1;
        $a = [$x1, $x, $x2];
        $b = [$y1, $y, $y2];

        $count = 0;
        foreach ($a as $v1) {
            foreach ($b as $v2) {
            	if ($v1 == $x && $v2 == $y) continue;
                if (isset($map[$v1][$v2]) && ($map[$v1][$v2] == 1)) {
                    $count++;
                }
            }
        }
        return $count;
    }

    public function Breed($data) {
    	$newdata = $data;
        foreach ($data as $x => $ys) {
            foreach ($ys as $y => $value) {
                // 如果这一点有细胞
                if ($data[$x][$y] == 1 || $data[$x][$y] == 2) {
                    $count = self::checkAround($x, $y, $data);
                    if ($count == 2) {
                        continue;
                    }
                    elseif ($count == 3) {
                        $newdata[$x][$y] = 1;
                    }
                    else {
                        $newdata[$x][$y] = 2;
                    }
                }
                else {
                    $count = self::checkAround($x, $y, $data);
                    if ($count == 3) {
                        $newdata[$x][$y] = 1;
                    }
                    else {
                        continue;
                    }
                }
            }
        }
        return $newdata;
    }
}