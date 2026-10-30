<?php
namespace App\Services;
use InvalidArgumentException;
final class PolygonGeofence {
 private array $polygons;
 public function __construct(array $g){$g=($g['type']??null)==='Feature'?($g['geometry']??[]):$g;$this->polygons=match($g['type']??null){'Polygon'=>[$g['coordinates']??[]],'MultiPolygon'=>$g['coordinates']??[],default=>throw new InvalidArgumentException('Polygon boundary required')};if(!$this->polygons)throw new InvalidArgumentException('Empty boundary');}
 public function contains(mixed $lat,mixed $lng):bool {if(!is_numeric($lat)||!is_numeric($lng)||!is_finite((float)$lat)||!is_finite((float)$lng)||abs((float)$lat)>90||abs((float)$lng)>180)return false;$x=(float)$lng;$y=(float)$lat;foreach($this->polygons as $p){if($this->ring($x,$y,$p[0])<0)continue;foreach(array_slice($p,1) as $h)if($this->ring($x,$y,$h)>=0)continue 2;return true;}return false;}
 private function ring(float $x,float $y,array $r):int{$in=false;for($i=1,$n=count($r);$i<$n;$i++){[$ax,$ay]=$r[$i-1];[$bx,$by]=$r[$i];$c=($x-$ax)*($by-$ay)-($y-$ay)*($bx-$ax);if(abs($c)<1e-12&&$x>=min($ax,$bx)&&$x<=max($ax,$bx)&&$y>=min($ay,$by)&&$y<=max($ay,$by))return 0;if(($ay>$y)!==($by>$y)&&$x<($bx-$ax)*($y-$ay)/($by-$ay)+$ax)$in=!$in;}return $in?1:-1;}
}
