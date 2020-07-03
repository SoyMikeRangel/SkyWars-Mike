<?php
declare(strict_types=1);
/* 
 * Author: @MikeRangelMR
 * Status: Private
 * Server: @PacmanLivePE 
*/
namespace MikeRangel\SkyWars\Arena;
use MikeRangel\SkyWars\{SkyWars, PluginUtils};
use pocketmine\{Server, Player, utils\Config, utils\TextFormat as Color};

class Arena {

    public static function getPlayers(string $arena) {
        $players = [];
		$expectedArena = Server::getInstance()->getLevelByName(self::getName($arena));
		if($expectedArena != null){
            foreach ($expectedArena->getPlayers() as $player) {
            	if ($player->getGamemode() == Player::SURVIVAL || $player->getGamemode() == Player::ADVENTURE) {
                	$players[] = $player->getName();
            	}
        	}
		}
        return $players;
    }

    public static function getSpecters(string $arena) {
        $specters = [];
		$expectedArena = Server::getInstance()->getLevelByName(self::getName($arena));
		if($expectedArena != null){
        	foreach ($expectedArena->getPlayers() as $player) {
            	if ($player->getGamemode() == Player::SPECTATOR) {
                	$specters[] = $player->getName();
            	}
        	}
		}
        return $specters;
    }

    public static function ArenaExiting(string $id) {
        if (file_exists(SkyWars::getInstance()->getDataFolder() . 'Arenas/SW-' . $id . '.yml')) {
            return false;
        } else {
            return true;
        }
    }

    public static function getArenas() : array {
        $arenas = [];
		if ($handle = opendir(SkyWars::getInstance()->getDataFolder() . 'Arenas/')) {
			while (false !== ($entry = readdir($handle))) {
				if ($entry !== '.' && $entry !== '..') {
					$name = str_replace('.yml', '', $entry);
                    $arenas[] = $name;
				}
			}
			closedir($handle);
		}
		return $arenas;
	}

    public static function addArena(Player $player, string $arena, string $slots, string $id) {
        Server::getInstance()->loadLevel($arena);
        Server::getInstance()->getLevelByName($arena)->loadChunk(Server::getInstance()->getLevelByName($arena)->getSafeSpawn()->getFloorX(), Server::getInstance()->getLevelByName($arena)->getSafeSpawn()->getFloorZ());
        $player->teleport(Server::getInstance()->getLevelByName($arena)->getSafeSpawn(), 0, 0);
        $player->setGamemode(1);
        SkyWars::$data['id'] = $id;
        SkyWars::$data['configurator'][] = $player->getName();
        SkyWars::$data['vote'][$arena]['op'] = [];
        SkyWars::$data['vote'][$arena]['normal'] = [];
        SkyWars::$data['kills'][$arena] = ['Steve' => 0, 'Enderman' => 0];
        PluginUtils::setZip($arena);
        $config = new Config(SkyWars::getInstance()->getDataFolder() . 'Arenas/SW-' . SkyWars::$data['id'] . '.yml', Config::YAML, [
            'arena' => $arena,
            'maxslots' => $slots,
            'status' => 'editing',
            'lobbytime' => 40,
            'startingtime' => 11,
            'gametime' => 600,
            'refilltime' => 120,
            'endtime' => 16
        ]);
        $config->save();
        $player->setGamemode(1);
        $player->sendMessage(SkyWars::getPrefix() . Color::GREEN . 'Arena created successfully.' . "\n" . Color::GREEN . 'You are now in configuration mode.');
    }

    public static function setLobbyWaiting(Player $player) {
        $config = SkyWars::getConfigs('Arenas/SW-' . SkyWars::$data['id']);
        $config->set('lobby', [
            $player->getX(),
            $player->getY(),
            $player->getZ()
        ]);
        $config->save();
        $player->sendMessage(Color::GREEN . 'Lobby registered with id: ' . SkyWars::$data['id']);
    }

    public static function setLobbySpecters(Player $player) {
        $config = SkyWars::getConfigs('Arenas/SW-' . SkyWars::$data['id']);
        $config->set('lobbyspecters', [
            $player->getX(),
            $player->getY(),
            $player->getZ()
        ]);
        $config->save();
        $player->sendMessage(Color::GREEN . 'Spectator lobby successfully registered to id: ' . SkyWars::$data['id']);
    }

    public static function setLobbyWin(Player $player) {
        $config = SkyWars::getConfigs('Arenas/SW-' . SkyWars::$data['id']);
        $config->set('lobbywin', [
            $player->getX(),
            $player->getY(),
            $player->getZ()
        ]);
        $config->save();
        $player->sendMessage(Color::GREEN . 'Win lobby successfully registered to id: ' . SkyWars::$data['id']);
    }

    public static function setSpawns(Player $player, string $value) {
        $config = SkyWars::getConfigs('Arenas/SW-' . SkyWars::$data['id']);
        $config->set('slot-' . $value, [
            $player->getX(),
            $player->getY(),
            $player->getZ()
        ]);
        $config->save();
        $player->sendMessage(Color::GREEN . 'Spawn-' . $value . ' registrado con exito en id: ' . SkyWars::$data['id']);
    }

    public static function getSpawns(string $arena) {
        $config = SkyWars::getConfigs('Arenas/' . $arena);
        return $config->get('maxslots');
    }

    public static function getName(string $arena) {
        $config = SkyWars::getConfigs('Arenas/' . $arena);
        return $config->get('arena');
    }

    public static function setStatus(string $arena, string $value) {
        $config = SkyWars::getConfigs('Arenas/' . $arena);
        $config->set('status', $value);
        $config->save();
    }

    public static function getStatus(string $arena) {
        $config = SkyWars::getConfigs('Arenas/' . $arena);
        return $config->get('status');
    }

    public static function setTimeWaiting(string $arena, int $value) {
        $config = SkyWars::getConfigs('Arenas/' . $arena);
        $config->set('lobbytime', $value);
        $config->save();
    }

    public static function getTimeWaiting(string $arena) {
        $config = SkyWars::getConfigs('Arenas/' . $arena);
        return $config->get('lobbytime');
    }

    public static function setTimeStarting(string $arena, int $value) {
        $config = SkyWars::getConfigs('Arenas/' . $arena);
        $config->set('startingtime', $value);
        $config->save();
    }

    public static function getTimeStarting(string $arena) {
        $config = SkyWars::getConfigs('Arenas/' . $arena);
        return $config->get('startingtime');
    }

    public static function setTimeGame(string $arena, int $value) {
        $config = SkyWars::getConfigs('Arenas/' . $arena);
        $config->set('gametime', $value);
        $config->save();
    }

    public static function getTimeGame(string $arena) {
        $config = SkyWars::getConfigs('Arenas/' . $arena);
        return $config->get('gametime');
    }

    public static function setTimeRefill(string $arena, int $value) {
        $config = SkyWars::getConfigs('Arenas/' . $arena);
        $config->set('refilltime', $value);
        $config->save();
    }

    public static function getTimeRefill(string $arena) {
        $config = SkyWars::getConfigs('Arenas/' . $arena);
        return $config->get('refilltime');
    }

    public static function setTimeEnd(string $arena, int $value) {
        $config = SkyWars::getConfigs('Arenas/' . $arena);
        $config->set('endtime', $value);
        $config->save();
    }

    public static function getTimeEnd(string $arena) {
        $config = SkyWars::getConfigs('Arenas/' . $arena);
        return $config->get('endtime');
    }
}
?>