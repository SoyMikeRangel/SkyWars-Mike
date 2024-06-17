<?php
declare(strict_types=1);
/* 
 * Author: @MikeRangelMR
 * Status: Private
 * Server: @PacmanLivePE 
*/
namespace MikeRangel\SkyWars\Tasks;
use MikeRangel\SkyWars\{SkyWars, Entity\types\EntityStats};
use pocketmine\{Server, player\Player, utils\TextFormat as Color, scheduler\Task};

class EntityStatsUpdate extends Task {

    public function onRun() : void {
        foreach (Server::getInstance()->getWorldManager()->getDefaultWorld()->getEntities() as $entity) {
            if ($entity instanceof EntityStats) {
                $entity->setNameTag(self::setTops());
                $entity->setNameTagAlwaysVisible(true);
            }
        }
    }

    public static function setTops() : string {
        $type = rand(1, 2) === 1 ? 'kills' : 'wins';
        $data = SkyWars::getConfigs($type);
        $allStats = $data->getAll();
        $tops = array_values($allStats);
        natsort($tops);
        $tops = array_reverse($tops);

        $title = Color::WHITE . Color::BOLD . '✘' . Color::RESET . Color::GOLD . 'Leaderboard SkyWars' . Color::WHITE . Color::BOLD . '✘' . Color::RESET . "\n";
        $subtitles = '';

        $position = 1;
        foreach ($tops as $topValue) {
            if ($topValue > 0) {
                $topPlayer = array_search($topValue, $allStats);
                $color = $position === 1 ? Color::GOLD : Color::DARK_AQUA;
                $stat = $type === 'kills' ? 'kills' : 'wins';
                $subtitles .= $color . "#$position " . Color::WHITE . $topPlayer . Color::GRAY . " - " . Color::AQUA . $topValue . $color . " $stat\n";
                $position++;
                if ($position > 10) break;
            }
        }

        return $title . $subtitles;
    }
}
?>
