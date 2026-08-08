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
use pmmp\encoding\LE;
use pmmp\encoding\VarInt;
use pocketmine\network\mcpe\protocol\serializer\CommonTypes;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use function count;

class SetScorePacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::SET_SCORE_PACKET;

	/** @var ScorePacketEntry[] */
	public array $entries = [];

	/**
	 * @generate-create-func
	 * @param ScorePacketEntry[] $entries
	 */
	public static function create(array $entries) : self{
		$result = new self;
		$result->entries = $entries;
		return $result;
	}

	protected function decodePayload(ByteBufferReader $in) : void{
		$count = VarInt::readUnsignedInt($in);
		for($i = 0, $i2 = $count; $i < $i2; ++$i){
			$entry = new ScorePacketEntry();
			$entry->type = VarInt::readUnsignedInt($in);
			CommonTypes::getString($in);
			$entry->scoreboardId = VarInt::readSignedLong($in);
			switch($entry->type){
				case ScorePacketEntry::TYPE_REMOVE:
					$entry->objectiveName = CommonTypes::readOptional($in, CommonTypes::getString(...));
				case ScorePacketEntry::TYPE_PLAYER:
				case ScorePacketEntry::TYPE_ENTITY:
					$entry->objectiveName = CommonTypes::getString($in);
					$entry->score = LE::readUnsignedInt($in);
					$entry->actorUniqueId = CommonTypes::getActorUniqueId($in);
					break;
				case ScorePacketEntry::TYPE_FAKE_PLAYER:
					$entry->objectiveName = CommonTypes::getString($in);
					$entry->score = LE::readUnsignedInt($in);
					$entry->customName = CommonTypes::getString($in);
					break;
				default:
					throw new PacketDecodeException("Unknown entry type $entry->type");
			}
			$this->entries[] = $entry;
		}
	}

	protected function encodePayload(ByteBufferWriter $out) : void{
		VarInt::writeUnsignedInt($out, count($this->entries));
		foreach($this->entries as $entry){
			VarInt::writeUnsignedInt($out, $entry->type);
				CommonTypes::putString($out, match ($entry->type) {
					ScorePacketEntry::TYPE_REMOVE => "remove",
					ScorePacketEntry::TYPE_PLAYER => "changeplayer",
					ScorePacketEntry::TYPE_ENTITY => "changeentity",
					ScorePacketEntry::TYPE_FAKE_PLAYER => "changefakeplayer",
					default => throw new \InvalidArgumentException("Unknown type $entry->type")
			});
			VarInt::writeSignedLong($out, $entry->scoreboardId);
			switch($entry->type){
				case ScorePacketEntry::TYPE_REMOVE:
					CommonTypes::writeOptional($out, $entry->objectiveName, static fn(ByteBufferWriter $out, string $value) => CommonTypes::putString($out, $value));
				case ScorePacketEntry::TYPE_PLAYER:
				case ScorePacketEntry::TYPE_ENTITY:
					CommonTypes::putString($out, $entry->objectiveName ?? throw new \InvalidArgumentException("Objective name must be set for player/entity entry"));
					LE::writeUnsignedInt($out, $entry->score);
					CommonTypes::putActorUniqueId($out, $entry->actorUniqueId);
					break;
				case ScorePacketEntry::TYPE_FAKE_PLAYER:
					CommonTypes::putString($out, $entry->objectiveName ?? throw new \InvalidArgumentException("Objective name must be set for player/entity entry"));
					LE::writeUnsignedInt($out, $entry->score);
					CommonTypes::putString($out, $entry->customName);
					break;
				default:
					throw new \InvalidArgumentException("Unknown entry type $entry->type");
			}
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleSetScore($this);
	}
}
