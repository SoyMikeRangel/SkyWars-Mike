<?php
declare(strict_types=1);
/*
 * Author: Mike Rangel
 * Version: 1.0.0
 * Status: Private
*/
namespace MikeRangel\SkyWars\Entity\types;
use pocketmine\{entity\Human};

class EntityHuman extends Human {

    public function getName() : string {
        return '';
    }
}
?>