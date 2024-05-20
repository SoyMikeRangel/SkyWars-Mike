<?php
/* 
 * Author: @MikeRangelMR
 * Status: Private
 * Server: @PacmanLivePE 
*/
namespace MikeRangel\SkyWars;
use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as Color;

class ResetMap {

    public static function resetZip(string $arena) {
        $server = Server::getInstance();
        $worldManager = $server->getWorldManager();

        if ($worldManager->isWorldLoaded($arena)) {
            $world = $worldManager->getWorldByName($arena);
            if ($world !== null) {
                $worldManager->unloadWorld($world);
            }
        }

        $zipPath = SkyWars::getInstance()->getDataFolder() . 'Backups' . DIRECTORY_SEPARATOR . $arena . '.zip';
        $zipArchive = new \ZipArchive();
        if ($zipArchive->open($zipPath) === true) {
            $zipArchive->extractTo($server->getDataPath() . 'worlds');
            $zipArchive->close();
            $server->getWorldManager()->loadWorld($arena);
            SkyWars::getInstance()->getLogger()->info(Color::GREEN . $arena . ' arena loaded with success.');
            return true;
        } else {
            SkyWars::getInstance()->getLogger()->error(Color::RED . 'Failed to open zip file: ' . $zipPath);
            return false;
        }
    }
}
?>
