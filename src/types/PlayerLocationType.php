<?php

/*
 * Copyright (C) 2014-2022 PocketMine Team <https://github.com/pmmp/BedrockProtocol>
 */

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol\types;

enum PlayerLocationType : int{
    case COORDINATES = 0;
    case HIDE = 1;
}