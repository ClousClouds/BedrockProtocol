<?php

/*
 * This file is part of BedrockProtocol.
 * Copyright (C) 2014-2022 PocketMine Team <https://github.com/pmmp/BedrockProtocol>
 *
 * BedrockProtocol is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol\types;

use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\ByteBufferWriter;
use pmmp\encoding\LE;
use pocketmine\network\mcpe\protocol\serializer\CommonTypes;

final class PlayerAuthInputVehicleInfo{

	public function __construct(
		private ?float $vehicleRotationX = null,
		private ?float $vehicleRotationZ = null,
		private ?int $predictedVehicleActorUniqueId = null
	){}

	public function getVehicleRotationX() : ?float{ return $this->vehicleRotationX; }

	public function getVehicleRotationZ() : ?float{ return $this->vehicleRotationZ; }

	public function getPredictedVehicleActorUniqueId() : ?int{ return $this->predictedVehicleActorUniqueId; }

	public static function read(ByteBufferReader $in) : self{
		$self = new self();

	if(CommonTypes::getBool($in) && CommonTypes::getBool($in)){
		$self->vehicleRotationX = LE::readFloat($in);
			$self->vehicleRotationZ = LE::readFloat($in);
	}
	if(CommonTypes::getBool($in) && CommonTypes::getBool($in)){
			$self->predictedVehicleActorUniqueId = CommonTypes::getActorUniqueId($in);
	}

		return new $self;
	}

	public function write(ByteBufferWriter $out) : void{
	if ($this->vehicleRotationX !== null && $this->vehicleRotationZ !== null) {
		CommonTypes::putBool($out, true);
		CommonTypes::putBool($out, true);
			LE::writeFloat($out, $this->vehicleRotationX);
			LE::writeFloat($out, $this->vehicleRotationZ);
	}else{
		CommonTypes::putBool($out, false);
		CommonTypes::putBool($out, false);
	}

	if($this->predictedVehicleActorUniqueId !== null){
		CommonTypes::putBool($out, true);
		CommonTypes::putBool($out, true);
		CommonTypes::putActorUniqueId($out, $this->predictedVehicleActorUniqueId);
	}else{
		CommonTypes::putBool($out, false);
		CommonTypes::putBool($out, false);
	}
	}
}
