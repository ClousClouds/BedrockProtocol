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

namespace pocketmine\network\mcpe\protocol;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\types\PlayerLocationType;

class PlayerLocationPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::PLAYER_LOCATION_PACKET;

	private PlayerLocationType $type;
	private int $actorId;
	private ?Vector3 $position = null;

	public static function coordinates(int $actorId, Vector3 $position) : self{
		$result = new self;
		$result->type = PlayerLocationType::COORDINATES;
		$result->actorId = $actorId;
		$result->position = $position;
		return $result;
	}

	public static function hide(int $actorId) : self{
		$result = new self;
		$result->type = PlayerLocationType::HIDE;
		$result->actorId = $actorId;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void{
		$this->type = PlayerLocationType::from($in->getVarInt());
		$this->actorId = $in->getActorUniqueId();
		if($this->type === PlayerLocationType::COORDINATES){
			$this->position = $in->getVector3();
		}
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putVarInt($this->type->value);
		$out->putActorUniqueId($this->actorId);
		if($this->type === PlayerLocationType::COORDINATES && $this->position !== null){
			$out->putVector3($this->position);
		}
	}

	public function getType() : PlayerLocationType{ return $this->type; }
	public function getActorId() : int{ return $this->actorId; }
	public function getPosition() : ?Vector3{ return $this->position; }

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handlePlayerLocation($this);
	}
}