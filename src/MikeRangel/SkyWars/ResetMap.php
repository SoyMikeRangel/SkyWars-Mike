<?php
/* 
 * Author: @MikeRangelMR
 * Status: Private
 * Server: @PacmanLivePE 
*/
namespace MikeRangel\SkyWars;

use pocketmine\Server;
use pocketmine\utils\TextFormat as Color;

class ResetMap {

    public static function resetZip(string $arena): bool {
        $server = Server::getInstance();
        $worldManager = $server->getWorldManager();

        if ($worldManager->isWorldLoaded($arena)) {
            $world = $worldManager->getWorldByName($arena);
            if ($world !== null) {
                $worldManager->unloadWorld($world, true);
            }
        }

        $zipPath = SkyWars::getInstance()->getDataFolder() . 'Backups' . DIRECTORY_SEPARATOR . $arena . '.zip';
        $zipArchive = new \ZipArchive();

        if ($zipArchive->open($zipPath) === true) {
            $worldPath = $server->getDataPath() . 'worlds' . DIRECTORY_SEPARATOR . $arena;
            self::deleteDirectory($worldPath);
            
            $zipArchive->extractTo($server->getDataPath() . 'worlds');
            $zipArchive->close();

            $worldManager->loadWorld($arena);
            SkyWars::getInstance()->getLogger()->info(Color::GREEN . $arena . ' arena loaded successfully.');
            return true;
        } else {
            SkyWars::getInstance()->getLogger()->error(Color::RED . 'Failed to open zip file: ' . $zipPath);
            return false;
        }
    }

    private static function deleteDirectory(string $dir): bool {
        if (!is_dir($dir)) {
            return false;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $filePath = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($filePath)) {
                self::deleteDirectory($filePath);
            } else {
                unlink($filePath);
            }
        }
        return rmdir($dir);
    }
}