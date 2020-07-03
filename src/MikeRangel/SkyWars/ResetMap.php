<?php
/* 
 * Author: @MikeRangelMR
 * Status: Private
 * Server: @PacmanLivePE 
*/
namespace MikeRangel\SkyWars;
use MikeRangel\SkyWars\{SkyWars};
use pocketmine\{Server, Player, utils\TextFormat as Color};

class ResetMap {

    public static function resetZip(string $arena) {
		if (Server::getInstance()->isLevelLoaded($arena)) {
			Server::getInstance()->unloadLevel(Server::getInstance()->getLevelByName($arena));
		}
		$zipPath = SkyWars::getInstance()->getDataFolder() . 'Backups' . DIRECTORY_SEPARATOR . $arena . '.zip';
		$zipArchive = new \ZipArchive();
		$zipArchive->open($zipPath);
		$zipArchive->extractTo(SkyWars::getInstance()->getServer()->getDataPath() . 'worlds');
		$zipArchive->close();
        Server::getInstance()->loadLevel($arena);
        SkyWars::getInstance()->getLogger()->info(Color::GOLD . $arena . ' arena loaded with success.');
		return true;
	}
}
?>