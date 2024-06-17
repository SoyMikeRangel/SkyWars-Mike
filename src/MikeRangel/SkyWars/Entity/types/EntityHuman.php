<?php
declare(strict_types=1);
/*
 * Author: Mike Rangel
 * Version: 1.0.0
 * Status: @PacmanLivePE
*/
namespace MikeRangel\SkyWars\Entity\types;
use pocketmine\entity\Human;
use pocketmine\world\World;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\entity\Skin;
use pocketmine\math\Vector3;
use pocketmine\entity\EntityDataHelper;

class EntityHuman extends Human {

    public function __construct(World $world, CompoundTag $nbt) {
        $skinTag = $nbt->getCompoundTag("Skin");
        $skin = new Skin(
            $skinTag->getString("Name"),
            $skinTag->getString("Data"),
            $skinTag->getString("CapeData"),
            $skinTag->getString("GeometryName"),
            $skinTag->getString("GeometryData")
        );

        $location = EntityDataHelper::parseLocation($nbt, $world);
        parent::__construct($location, $skin);
        $this->setNameTagVisible(true);
        $this->setNameTagAlwaysVisible(true);
    }

    public function getName(): string {
        return '';
    }
}
?>