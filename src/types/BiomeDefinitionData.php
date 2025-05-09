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

use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;

final class BiomeDefinitionData{
	public function __construct(
		private int $biomeId,
		private float $temperature,
		private float $downfall,
		private int $waterColor
	){}

	public static function read(PacketSerializer $in) : self{
		return new self(
			$in->getLShort(),
			$in->getLFloat(),
			$in->getLFloat(),
			$in->getLInt()
		);
	}

	public function write(PacketSerializer $out) : void{
		$out->putLShort($this->biomeId);
		$out->putLFloat($this->temperature);
		$out->putLFloat($this->downfall);
		$out->putLInt($this->waterColor);
	}

	public function getBiomeId() : int{ return $this->biomeId; }
	public function getTemperature() : float{ return $this->temperature; }
	public function getDownfall() : float{ return $this->downfall; }
	public function getWaterColor() : int{ return $this->waterColor; }
}
