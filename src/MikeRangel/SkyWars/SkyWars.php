<?php
declare(strict_types=1);
/* 
 * Author: @MikeRangelMR
 * Status: Private
 * Server: @PacmanLivePE 
*/
namespace MikeRangel\SkyWars;
use MikeRangel\SkyWars\{Arena\Arena, API\ScoreAPI, Executor\Commands, Tasks\EntityStatsUpdate, Tasks\NewMap, Events\GlobalEvents, Tasks\GameScheduler, Tasks\EntityUpdate, Entity\types\EntityHuman, Entity\types\EntityStats};
use pocketmine\{Server, player\Player, world\WorldManager, world\World, nbt\tag\CompoundTag, plugin\PluginBase, entity\Entity, entity\Human, entity\EntityFactory, entity\EntityDataHelper, utils\Config, utils\TextFormat as Color};

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
        'click' => [],
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
        $config->set('lobbytime', 30);
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
    }

    public function loadEntitys(): void {
        $entities = [
            EntityHuman::class => 'entityhuman',
            EntityStats::class => 'entitystats'
        ];
        foreach ($entities as $entityClass => $entityId) {
            EntityFactory::getInstance()->register($entityClass, function(World $world, CompoundTag $nbt) use ($entityClass): Entity {
                $location = EntityDataHelper::parseLocation($nbt, $world);
                $skin = Human::parseSkinNBT($nbt);
                return new $entityClass($location, $skin, $nbt);
            }, [$entityId]);
        }
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
        $this->getScheduler()->scheduleRepeatingTask(new EntityStatsUpdate($this), 2 * 25);
        unset($values);
    }

    public function onDisable() : void {
        $this->getLogger()->info(Color::RED . 'Plugin disabled.');
    }
}