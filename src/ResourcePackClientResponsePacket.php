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

use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\ByteBufferWriter;
use pmmp\encoding\VarInt;
use pocketmine\network\mcpe\protocol\serializer\CommonTypes;
use pocketmine\network\mcpe\protocol\types\resourcepacks\ResourcePackClientResponse;
use pocketmine\network\mcpe\protocol\types\resourcepacks\ResourcePackClientResponseType;

class ResourcePackClientResponsePacket extends DataPacket implements ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::RESOURCE_PACK_CLIENT_RESPONSE_PACKET;

	public ResourcePackClientResponse $response;

	/**
	 * @generate-create-func
	 */
	public static function create(ResourcePackClientResponse $response) : self{
		$result = new self;
		$result->response = $response;
		return $result;
	}

	public function getResponse() : ResourcePackClientResponse{
		return $this->response;
	}

	protected function decodePayload(ByteBufferReader $in) : void{
		VarInt::readUnsignedInt($in);
		CommonTypes::getString($in);

		$this->response = ResourcePackClientResponse::read($in);
	}

	protected function encodePayload(ByteBufferWriter $out) : void{
		VarInt::writeUnsignedInt($out, $this->response->getType()->value);
		CommonTypes::putString($out, match($this->response->getType()){
				ResourcePackClientResponseType::CANCEL => "cancel",
				ResourcePackClientResponseType::DOWNLOADING => "downloading",
				ResourcePackClientResponseType::DOWNLOADING_FINISHED => "downloadingfinished",
				ResourcePackClientResponseType::RESOURCE_PACK_STACK_FINISHED => "resourcepackstackfinished",
		});
		$this->response->write($out);
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleResourcePackClientResponse($this);
	}
}
