<?php
declare(strict_types=1);
/*
 * Author: Mike Rangel
 * Version: 1.0.0
 * Status: @PacmanLivePE
*/
namespace MikeRangel\SkyWars\Entity\types;

use pocketmine\entity\Human;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\entity\Skin;
use pocketmine\entity\Location;

class EntityHuman extends Human {

    public function __construct(Location $location, Skin $skin, CompoundTag $nbt) {
        parent::__construct($location, $skin);
        $this->setNameTagVisible(true);
        $this->setNameTagAlwaysVisible(true);
        $this->initialize();
    }

    public function initialize() {
        $this->setNameTag("EntityHuman");
    }

    public function getName(): string {
        return 'EntityHuman';
    }
}