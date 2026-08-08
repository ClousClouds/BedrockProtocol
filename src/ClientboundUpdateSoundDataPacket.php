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
use pocketmine\network\mcpe\protocol\types\sound\ServerSoundHandle;
use pocketmine\network\mcpe\protocol\types\sound\SoundControl;

final class ClientboundUpdateSoundDataPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::CLIENTBOUND_UPDATE_SOUND_DATA_PACKET;

	private ServerSoundHandle $serverSoundHandle;
	private SoundControl $soundControl;

	/**
	 * @generate-create-func
	 */
	public static function create(ServerSoundHandle $serverSoundHandle, SoundControl $soundControl) : self{
		$result = new self;
		$result->serverSoundHandle = $serverSoundHandle;
		$result->soundControl = $soundControl;
		return $result;
	}

	public function getServerSoundHandle() : ServerSoundHandle{
		return $this->serverSoundHandle;
	}

	public function getSoundControl() : SoundControl{
		return $this->soundControl;
	}

	protected function decodePayload(ByteBufferReader $in) : void{
		$this->serverSoundHandle = ServerSoundHandle::read($in);
		$this->soundControl = SoundControl::read($in);
	}

	protected function encodePayload(ByteBufferWriter $out) : void{
		$this->serverSoundHandle->write($out);
		$this->soundControl->write($out);
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleClientboundUpdateSoundData($this);
	}
}
