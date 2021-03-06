<?php
declare(strict_types=1);
/* 
 * Author: @MikeRangelMR
 * Status: Private
 * Server: @PacmanLivePE 
*/
namespace MikeRangel\SkyWars;
use MikeRangel\SkyWars\{Arena\Arena, API\ScoreAPI, Executor\Commands, Tasks\EntityStatsUpdate, Tasks\NewMap, Events\GlobalEvents, Tasks\GameScheduler, Tasks\EntityUpdate, Entity\types\EntityHuman, Entity\types\EntityStats};
use pocketmine\{Server, Player, plugin\PluginBase, entity\Entity, utils\Config, utils\TextFormat as Color};

class SkyWars extends PluginBase {
    public static $instance;
    public static $score;
    public static $data = [
        'prefix' => Color::GOLD . '[SkyWars] ',
        'id' => '',
        'vote' => [],
        'kills' => [],
        'damager' => [],
        'emotes' => [],
        'queue' => [],
        'skins' => [],
        'configurator' => []
    ];

    public function onLoad() : void {
        self::$instance = $this;
        self::$score = new ScoreAPI($this);
    }

    public function onEnable() : void {
        $this->saveResources();
        $this->getLogger()->info(Color::GREEN . 'Plugin activated successfully.');
        foreach (Arena::getArenas() as $arena) {
            if (count(Arena::getArenas()) > 0) {
                self::getReloadArena($arena);
                ResetMap::resetZip(Arena::getName($arena));
            }
        }
        $this->getScheduler()->scheduleRepeatingTask(new EntityStatsUpdate($this), 2 * 25);
        $this->loadEntitys();
        $this->loadCommands();
        $this->loadEvents();
        $this->loadTasks();
    }

    public static function getInstance() : self {
        return self::$instance;
    }

    public static function getPrefix() : string {
        return self::$data['prefix'];
    }

    public static function getScore() : ScoreAPI {
		return self::$score;
	}

    public static function getConfigs(string $value) : config {
		return new Config(self::getInstance()->getDataFolder() . "{$value}.yml", Config::YAML);
    }

    public static function getReloadArena(string $arena) {
        $config = self::getConfigs('Arenas/' . $arena);
        self::$data['vote'][Arena::getName($arena)]['op'] = [];
        self::$data['vote'][Arena::getName($arena)]['normal'] = [];
        self::$data['kills'][Arena::getName($arena)] = ['Steve' => 0, 'Enderman' => 0];
        $config->set('status', 'waiting');
        $config->set('lobbytime', 40);
        $config->set('startingtime', 11);
        $config->set('gametime', 600);
        $config->set('refilltime', 120);
        $config->set('endtime', 16);
        $config->save();
    }

    public function saveResources() : void {
        $folder = $this->getDataFolder();
        foreach([$folder, $folder . 'Arenas', $folder . 'Backups', $folder . 'Profiles',
        $folder . 'Emotes', $folder . 'Cages'] as $dir) {
            if (!is_dir($dir))
            @mkdir($dir);
        } foreach (['NPC.png', 'NPC.json', 'config.yml', 'buycages.yml', 'buyemotes.yml', 'kills.yml', 'wins.yml'] as $files) {
            $this->saveResource($files);
        } foreach (['Dab.json'] as $files) {
            $this->saveResource('Emotes/' . $files);
        } foreach (['Aquatic.yml', 'Colors.yml', 'Carcel.yml', 'Desert.yml', 'Globo.yml', 'Hueso.yml', 'Infra.yml',
        'Medieval.yml', 'Minas.yml', 'Moral.yml', 'Nieve.yml', 'Ocean.yml', 'PrysmaPurple.yml', 'Selva.yml',
        'Spawner.yml', 'Temple.yml', 'Tree.yml', 'TreeLight.yml', 'Woold.yml'] as $files) {
            $this->saveResource('Cages/' . $files);
        }
        $config = self::getConfigs('config');
        if ($config->get('chestitems') == null || $config->get('chestitemsop') == null) {
            $config->set('chestitemsop', [[306, 0, 1], [307, 0, 1], [308, 0, 1], [309, 0, 1], [309, 0, 1], [310, 0, 1], [311, 0, 1],
            [312, 0, 1], [313, 0, 1], [267, 0, 1], [276, 0, 1], [279, 0, 1], [278, 0, 1], [257, 0, 1], [322, 0, 2], [322, 0, 4], [368, 0, 1],
            [364, 0, 3], [364, 0, 7], [1, 0, 32], [5, 0, 32], [261, 0, 1], [262, 0, 15], [262, 0, 10], [332, 0, 7], [332, 0, 14], [345, 0, 1],
            [1, 0, 32], [5, 0, 32], [46, 0, 3], [259, 0, 1], [325, 8, 1], [325, 8, 1], [325, 10, 1], [325, 10, 1]]);
            $config->save();
            $config->set('chestitems', [[306, 0, 1], [307, 0, 1], [308, 0, 1], [309, 0, 1], [309, 0, 1], [310, 0, 1], [311, 0, 1],
            [312, 0, 1], [313, 0, 1], [267, 0, 1], [276, 0, 1], [279, 0, 1], [278, 0, 1], [257, 0, 1], [322, 0, 2], [322, 0, 4], [368, 0, 1],
            [364, 0, 3], [364, 0, 7], [1, 0, 32], [5, 0, 32], [261, 0, 1], [262, 0, 15], [262, 0, 10], [332, 0, 7], [332, 0, 14], [345, 0, 1],
            [1, 0, 32], [5, 0, 32], [46, 0, 3], [259, 0, 1], [325, 8, 1], [325, 8, 1], [325, 10, 1], [325, 10, 1]]);
            $config->save();
        }
    }
    
    public function loadEntitys() : void {
		$values = [EntityHuman::class, EntityStats::class];
		foreach ($values as $entitys) {
			Entity::registerEntity($entitys, true);
		}
		unset ($values);
	}

	public function loadCommands() : void {
		$values = [new Commands($this)];
		foreach ($values as $commands) {
			$this->getServer()->getCommandMap()->register('_cmd', $commands);
		}
		unset($values);
	}

	public function loadEvents() : void {
		$values = [new GlobalEvents($this)];
		foreach ($values as $events) {
			$this->getServer()->getPluginManager()->registerEvents($events, $this);
		}
		unset($values);
	}

	public function loadTasks() : void {
		$values = [new GameScheduler($this), new EntityUpdate($this)];
		foreach ($values as $tasks) {
			$this->getScheduler()->scheduleRepeatingTask($tasks, 20);
		}
        unset($values);
    }

    public function onDisable() : void {
        $this->getLogger()->info(Color::RED . 'Plugin disabled.');
    }
}
?>