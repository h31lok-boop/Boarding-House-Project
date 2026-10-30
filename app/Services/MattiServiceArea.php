<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
final class MattiServiceArea {
 private PolygonGeofence $f; private array $feature;
 public function __construct(){ $this->feature=json_decode(file_get_contents(config('service-area.boundary_path')),true,512,JSON_THROW_ON_ERROR);$this->f=new PolygonGeofence($this->feature); }
 public function contains($lat,$lng):bool{return $this->f->contains($lat,$lng);}
 public function validate($lat,$lng,$field='latitude'):void{if(!$this->contains($lat,$lng))throw ValidationException::withMessages([$field=>'The boarding house must be located inside '.config('service-area.name').'.']);}
 public function eligibleIds(?string $connection=null):array{if(!Schema::connection($connection)->hasTable('boarding_houses'))return []; $c=$this->mapConfig()['bounds'];return DB::connection($connection)->table('boarding_houses')->whereBetween('latitude',[$c[0][1],$c[1][1]])->whereBetween('longitude',[$c[0][0],$c[1][0]])->get(['id','latitude','longitude'])->filter(fn($h)=>$this->contains($h->latitude,$h->longitude))->pluck('id')->map(fn($id)=>(int)$id)->all();}
 public function mapConfig():array{$g=$this->feature['geometry']??$this->feature;$ps=$g['type']==='Polygon'?[$g['coordinates']]:$g['coordinates'];$p=array_merge(...array_map(fn($x)=>$x[0],$ps));$xs=array_column($p,0);$ys=array_column($p,1);$configuredCenter=[(float) config('service-area.center.longitude',125.3054672),(float) config('service-area.center.latitude',6.7718373)];$center=$this->contains($configuredCenter[1],$configuredCenter[0])?$configuredCenter:[[array_sum($xs)/count($xs),array_sum($ys)/count($ys)]][0];return ['name'=>config('service-area.name'),'geometry'=>$g,'bounds'=>[[min($xs),min($ys)],[max($xs),max($ys)]],'center'=>$center,'referenceUrl'=>config('service-area.reference_url'),'version'=>hash('sha256',json_encode($g))];}
}
