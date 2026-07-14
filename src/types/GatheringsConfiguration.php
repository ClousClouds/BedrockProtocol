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
use pocketmine\network\mcpe\protocol\serializer\CommonTypes;
use Ramsey\Uuid\UuidInterface;

final class GatheringsConfiguration{

	public function __construct(
		public UuidInterface $experienceId,
    public string $experienceName,
    public UuidInterface $worldId,
    public string $worldName,
    public string $creatorId,
    public UuidInterface $targetId,
    public string $scenarioId,
    public string $serverId,
	){}

  public function getExperienceId() : UuidInterface { return $this->experienceId; }

  public function getExperienceName() : string { return $this->experienceName; }

  public function getWorldId() : UuidInterface { return $this->worldId; }

  public function getWorldName() : string { return $this->worldName; }

  public function getCreatorId() : string { return $this->creatorId; }

  public function getTargetId() : UuidInterface { return $this->targetId; }

  public function getScenarioId() : string { return $this->scenarioId; }

  public function getServerId() : string { return $this->serverId; }

	public static function read(ByteBufferReader $in) : self{
		$experienceId = CommonTypes::getUUID($in);
    $experienceName = CommonTypes::getString($in);
    $worldId = CommonTypes::getUUID($in);
    $worldName = CommonTypes::getString($in);
    $creatorId = CommonTypes::getString($in);
    $targetId = CommonTypes::getUUID($in);
    $scenarioId = CommonTypes::getString($in);
    $serverId = CommonTypes::getString($in);

		return new self(
			$experienceId,
      $experienceName,
      $worldId,
      $worldName,
      $creatorId,
      $targetId,
      $scenarioId,
      $serverId,
		);
	}

	public function write(ByteBufferWriter $out) : void{
		CommonTypes::putUUID($out, $this->experienceId);
    CommonTypes::putString($out, $this->experienceName);
		CommonTypes::putUUID($out, $this->worldId);
    CommonTypes::putString($out, $this->worldName);
    CommonTypes::putString($out, $this->creatorId);
		CommonTypes::putUUID($out, $this->targetId);
    CommonTypes::putString($out, $this->scenarioId);
    CommonTypes::putString($out, $this->serverId);
	}
}
