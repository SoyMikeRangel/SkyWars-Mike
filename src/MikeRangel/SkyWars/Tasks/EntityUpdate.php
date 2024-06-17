<?php
declare(strict_types=1);
/* 
 * Author: @MikeRangelMR
 * Status: Private
 * Server: @PacmanLivePE 
*/
namespace MikeRangel\SkyWars\Tasks;
use MikeRangel\SkyWars\{SkyWars, Arena\Arena, Entity\types\EntityHuman};
use pocketmine\{Server, player\Player, utils\TextFormat as Color, entity\effect\EffectInstance, entity\effect\VanillaEffects, scheduler\Task};

class EntityUpdate extends Task {
    public function onRun(): void {
        $defaultWorld = Server::getInstance()->getWorldManager()->getDefaultWorld();
        foreach ($defaultWorld->getEntities() as $entity) {
            if ($entity instanceof EntityHuman) {
                $entity->getEffects()->add(new EffectInstance(VanillaEffects::FIRE_RESISTANCE(), 999));
                $entity->setNameTag(self::setName());
                $entity->setNameTagAlwaysVisible(true);
                $entity->setScale(2);
            }
        }
    }

    public static function setName(): string {
        $colors = [Color::AQUA . '[Beta]', Color::AQUA . '[1.0.0]'];
        $title = Color::GREEN . Color::BOLD . '»' . Color::GRAY . 'NEW GAME' . Color::GREEN . '«' . "\n";
        $subtitle1 = Color::BOLD . Color::GOLD . 'SKYWARS ' . Color::RESET . $colors[array_rand($colors)] . "\n";
        $subtitle2 = Color::GREEN . self::getAllPlayers() . ' now playing';
        return $title . $subtitle1 . $subtitle2;
    }

    public static function getAllPlayers(): int {
        $players = [];
        foreach (Arena::getArenas() as $arena) {
            $world = Server::getInstance()->getWorldManager()->getWorldByName(Arena::getName($arena));
            if ($world !== null) {
                foreach ($world->getPlayers() as $player) {
                    array_push($players, $player->getName());
                }
            }
        }
        return count($players);
    }
}
?>