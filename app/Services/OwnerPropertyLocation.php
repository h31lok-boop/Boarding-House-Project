<?php
namespace App\Services;
use App\Services\MattiServiceArea;
use Illuminate\Validation\ValidationException;
final class OwnerPropertyLocation {
 public function resolve(string $address,mixed $lat=null,mixed $lng=null):array {if($lat===null||$lng===null)throw ValidationException::withMessages(['bh_address'=>'Add the boarding house latitude and longitude from the map. The location must be inside '.config('service-area.name').'.']);app(MattiServiceArea::class)->validate($lat,$lng,'bh_address');return ['latitude'=>(float)$lat,'longitude'=>(float)$lng];}
}
