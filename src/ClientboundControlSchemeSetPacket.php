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

use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;

class ClientboundControlSchemeSetPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::CLIENTBOUND_CONTROL_SCHEME_SET_PACKET;

	private int $controlScheme;

	public static function create(int $controlScheme) : self{
		$result = new self;
		$result->controlScheme = $controlScheme;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void{
		$this->controlScheme = $in->getUnsignedVarInt();
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putUnsignedVarInt($this->controlScheme);
	}

	public function getControlScheme() : int{ return $this->controlScheme; }

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleClientboundControlSchemeSet($this);
	}
}
