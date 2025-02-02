<?php
/**
 * Created by PhpStorm.
 * User: Samuel
 * Date: 03.03.2019
 * Time: 17:41
 */

namespace Core\FFA\Scheduler;


use Core\FFA\Main;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\Config;

class TitleScheduler extends Task {

    /** @var Main  */
    private $main;
    protected $player;

    public function __construct(Main $main, Player $player) {
        $this->main = $main;
        $this->player = $player;
    }

    public function onRun(int $currentTick) {
        $config = new Config($this->main->getDataFolder() . "config.yml", Config::YAML);

        $this->player->addTitle($config->get("Title"), $config->get("Subtitle"));

        $this->main->getScheduler()->scheduleRepeatingTask(new Scoreboard($this->main, $this->player), 10);
    }
}
