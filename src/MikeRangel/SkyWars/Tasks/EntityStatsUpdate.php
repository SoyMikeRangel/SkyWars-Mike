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
        foreach (Server::getInstance()->getWorldManager()->getDefaultWorld()->getPlayers() as $entity) {
			if ($entity instanceof EntityStats) {
                $entity->setNameTag(self::setTops());
                $entity->setNameTagAlwaysVisible(true);
            }
        }
    }

    public static function setTops() : string {
		switch (rand(1, 2)) {
			case 1:
				$kills = SkyWars::getConfigs('kills');
				$tops = [];
				$title = Color::WHITE . Color::BOLD . '✘' . Color::RESET . Color::GOLD . 'Leaderboard SkyWars' . Color::WHITE . Color::BOLD . '✘' . Color::RESET . "\n";
				foreach ($kills->getAll() as $key => $top) {
					array_push($tops, $top);
				}
				natsort($tops);
				$player = array_reverse($tops);
				if (max($tops) != null) {
					$top1 = array_search(max($tops), $kills->getAll());
					$subtitle1 = Color::GOLD . '#1 ' . Color::WHITE . $top1 . Color::GRAY . ' - ' . Color::AQUA . max($tops) . Color::GOLD . ' kills' . "\n";
				} else {
					$subtitle1 = '';
				}
				if ($player[1] != null) {
					$top2 = array_search($player[1], $kills->getAll());
					$subtitle2 = Color::DARK_AQUA . '#2 ' . Color::WHITE . $top2 . Color::GRAY . ' - ' . Color::AQUA . $player[1] . Color::DARK_AQUA . ' kills' . "\n";
				} else {
					$subtitle2 = '';
				}
				if ($player[2] != null) {
					$top3 = array_search($player[2], $kills->getAll());
					$subtitle3 = Color::DARK_AQUA . '#3 ' . Color::WHITE . $top3 . Color::GRAY . ' - ' . Color::AQUA . $player[2] . Color::DARK_AQUA . ' kills' . "\n";
				} else {
					$subtitle3 = '';
				}
				if ($player[3] != null) {
					$top4 = array_search($player[3], $kills->getAll());
					$subtitle4 = Color::DARK_AQUA . '#4 ' . Color::WHITE . $top4 . Color::GRAY . ' - ' . Color::AQUA . $player[3] . Color::DARK_AQUA . ' kills' . "\n";
				} else {
					$subtitle4 = '';
				}
				if ($player[4] != null) {
					$top5 = array_search($player[4], $kills->getAll());
					$subtitle5 = Color::DARK_AQUA . '#5 ' . Color::WHITE . $top5 . Color::GRAY . ' - ' . Color::AQUA . $player[4] . Color::DARK_AQUA . ' kills' . "\n";
				} else {
					$subtitle5 = '';
				}
				if ($player[5] != null) {
					$top6 = array_search($player[5], $kills->getAll());
					$subtitle6 = Color::DARK_AQUA . '#6 ' . Color::WHITE . $top6 . Color::GRAY . ' - ' . Color::AQUA . $player[5] . Color::DARK_AQUA . ' kills' . "\n";
				} else {
					$subtitle6 = '';
				}
				if ($player[6] != null) {
					$top7 = array_search($player[6], $kills->getAll());
					$subtitle7 = Color::DARK_AQUA . '#7 ' . Color::WHITE . $top7 . Color::GRAY . ' - ' . Color::AQUA . $player[6] . Color::DARK_AQUA . ' kills' . "\n";
				} else {
					$subtitle7 = '';
				}
				if ($player[7] != null) {
					$top8 = array_search($player[7], $kills->getAll());
					$subtitle8 = Color::DARK_AQUA . '#8 ' . Color::WHITE . $top8 . Color::GRAY . ' - ' . Color::AQUA . $player[7] . Color::DARK_AQUA . ' kills' . "\n";
				} else {
					$subtitle8 = '';
				}
				if ($player[8] != null) {
					$top9 = array_search($player[8], $kills->getAll());
					$subtitle9 = Color::DARK_AQUA . '#9 ' . Color::WHITE . $top9 . Color::GRAY . ' - ' . Color::AQUA . $player[8] . Color::DARK_AQUA . ' kills' . "\n";
				} else {
					$subtitle9 = '';
				}
				if ($player[9] != null) {
					$top10 = array_search($player[9], $kills->getAll());
					$subtitle10 = Color::DARK_AQUA . '#10 ' . Color::WHITE . $top10 . Color::GRAY . ' - ' . Color::AQUA . $player[9] . Color::DARK_AQUA . ' kills' . "\n";
				} else {
					$subtitle10 = '';
				}
				return $title . $subtitle1 . $subtitle2 . $subtitle3 . $subtitle4 . $subtitle5 . $subtitle6 . $subtitle7 . $subtitle8 . $subtitle9 . $subtitle10;		
			break;
			case 2:
				$wins = SkyWars::getConfigs('wins');
				$tops = [];
				$title = Color::WHITE . Color::BOLD . '✘' . Color::RESET . Color::GOLD . 'Leaderboard SkyWars' . Color::WHITE . Color::BOLD . '✘' . Color::RESET . "\n";
				foreach ($wins->getAll() as $key => $top) {
					array_push($tops, $top);
				}
				natsort($tops);
				$player = array_reverse($tops);
				if (max($tops) != null) {
					$top1 = array_search(max($tops), $wins->getAll());
					$subtitle1 = Color::GOLD . '#1 ' . Color::WHITE . $top1 . Color::GRAY . ' - ' . Color::AQUA . max($tops) . Color::GOLD . ' wins' . "\n";
				} else {
					$subtitle1 = '';
				}
				if ($player[1] != null) {
					$top2 = array_search($player[1], $wins->getAll());
					$subtitle2 = Color::DARK_AQUA . '#2 ' . Color::WHITE . $top2 . Color::GRAY . ' - ' . Color::AQUA . $player[1] . Color::DARK_AQUA . ' wins' . "\n";
				} else {
					$subtitle2 = '';
				}
				if ($player[2] != null) {
					$top3 = array_search($player[2], $wins->getAll());
					$subtitle3 = Color::DARK_AQUA . '#3 ' . Color::WHITE . $top3 . Color::GRAY . ' - ' . Color::AQUA . $player[2] . Color::DARK_AQUA . ' wins' . "\n";
				} else {
					$subtitle3 = '';
				}
				if ($player[3] != null) {
					$top4 = array_search($player[3], $wins->getAll());
					$subtitle4 = Color::DARK_AQUA . '#4 ' . Color::WHITE . $top4 . Color::GRAY . ' - ' . Color::AQUA . $player[3] . Color::DARK_AQUA . ' wins' . "\n";
				} else {
					$subtitle4 = '';
				}
				if ($player[4] != null) {
					$top5 = array_search($player[4], $wins->getAll());
					$subtitle5 = Color::DARK_AQUA . '#5 ' . Color::WHITE . $top5 . Color::GRAY . ' - ' . Color::AQUA . $player[4] . Color::DARK_AQUA . ' wins' . "\n";
				} else {
					$subtitle5 = '';
				}
				if ($player[5] != null) {
					$top6 = array_search($player[5], $wins->getAll());
					$subtitle6 = Color::DARK_AQUA . '#6 ' . Color::WHITE . $top6 . Color::GRAY . ' - ' . Color::AQUA . $player[5] . Color::DARK_AQUA . ' wins' . "\n";
				} else {
					$subtitle6 = '';
				}
				if ($player[6] != null) {
					$top7 = array_search($player[6], $wins->getAll());
					$subtitle7 = Color::DARK_AQUA . '#7 ' . Color::WHITE . $top7 . Color::GRAY . ' - ' . Color::AQUA . $player[6] . Color::DARK_AQUA . ' wins' . "\n";
				} else {
					$subtitle7 = '';
				}
				if ($player[7] != null) {
					$top8 = array_search($player[7], $wins->getAll());
					$subtitle8 = Color::DARK_AQUA . '#8 ' . Color::WHITE . $top8 . Color::GRAY . ' - ' . Color::AQUA . $player[7] . Color::DARK_AQUA . ' wins' . "\n";
				} else {
					$subtitle8 = '';
				}
				if ($player[8] != null) {
					$top9 = array_search($player[8], $wins->getAll());
					$subtitle9 = Color::DARK_AQUA . '#9 ' . Color::WHITE . $top9 . Color::GRAY . ' - ' . Color::AQUA . $player[8] . Color::DARK_AQUA . ' wins' . "\n";
				} else {
					$subtitle9 = '';
				}
				if ($player[9] != null) {
					$top10 = array_search($player[9], $wins->getAll());
					$subtitle10 = Color::DARK_AQUA . '#10 ' . Color::WHITE . $top10 . Color::GRAY . ' - ' . Color::AQUA . $player[9] . Color::DARK_AQUA . ' wins' . "\n";
				} else {
					$subtitle10 = '';
				}
				return $title . $subtitle1 . $subtitle2 . $subtitle3 . $subtitle4 . $subtitle5 . $subtitle6 . $subtitle7 . $subtitle8 . $subtitle9 . $subtitle10;		
			break;	
		}
	}
}
?>