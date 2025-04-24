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

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\types\BiomeDefinitionData;
use pocketmine\network\mcpe\protocol\types\BiomeStringIndex;
use pocketmine\network\mcpe\protocol\types\BiomeStringList;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use function count;

class BiomeDefinitionListPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::BIOME_DEFINITION_LIST_PACKET;

	/** @phpstan-var CacheableNbt<CompoundTag> */
	private CacheableNbt $definitions;
	/** @phpstan-var array<int, BiomeDefinitionData> */
	private array $biomeData = [];
	private BiomeStringList $stringList;

	/**
	 * @phpstan-param CacheableNbt<CompoundTag> $definitions
	 * @phpstan-param array<int, BiomeDefinitionData> $biomeDats
	 */
	public static function create(CacheableNbt $definitions, array $biomeData, BiomeStringList $stringList) : self{
		$result = new self;
		$result->definitions = $definitions;
		$result->biomeData = $biomeData;
		$result->stringList = $stringList;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void{
		$this->definitions = new CacheableNbt($in->getNbtCompoundRoot());

		$count = $in->getUnsignedVarInt();
		for($i = 0; $i < $count; $i++){
			$index = BiomeStringIndex::read($in);
			$this->biomeData[$index->getIndex()] = BiomeDefinitionData::read($in);
		}
		$this->stringList = BiomeStringList::read($in);

		$nbt = $this->definitions->getRoot();
		if(($biomeDataTag = $nbt->getTag("biome_data")) instanceof ListTag){
			foreach($biomeDataTag as $tag){
				if($tag instanceof CompoundTag){
					// Assuming biome ID is stored in the NBT
					$biomeId = $tag->getInt("biome_id", 0);
					$temperature = $tag->getFloat("temperature", 0.0);
					$downfall = $tag->getFloat("downfall", 0.0);
					$waterColor = $tag->getInt("water_color", 0);
					$this->biomeData[] = new BiomeDefinitionData($biomeId, $temperature, $downfall, $waterColor);
				}
			}
		}
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$nbt = $this->definitions->getRoot();

		$biomeDataList = [];
		foreach($this->biomeData as $data){
			$tag = new CompoundTag();
			$tag->setInt("biome_id", $data->getBiomeId());
			$tag->setFloat("temperature", $data->getTemperature());
			$tag->setFloat("downfall", $data->getDownfall());
			$tag->setInt("water_color", $data->getWaterColor());
			$biomeDataList[] = $tag;
		}
		$nbt->setTag("biome_data", new ListTag($biomeDataList));

		$out->put($this->definitions->getEncodedNbt());
		$out->putUnsignedVarInt(count($this->biomeData));
		foreach($this->biomeData as $index => $data){
			(new BiomeStringIndex($index))->write($out);
			$data->write($out);
		}
		$this->stringList->write($out);
	}

	/** @return array<int, BiomeDefinitionData> */
	public function getBiomeData() : array{ return $this->biomeData; }

	public function getStringList() : BiomeStringList{ return $this->stringList; }

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleBiomeDefinitionList($this);
	}
}
