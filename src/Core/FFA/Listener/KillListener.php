<?php
/**
 * Created by PhpStorm.
 * User: Samuel
 * Date: 03.03.2019
 * Time: 16:21
 */

namespace Core\FFA\Listener;


use Core\FFA\Main;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class KillListener implements Listener {

    /** @var Main  */
    private $main;

    public function __construct(Main $main) {
        $this->main = $main;
    }

    public function onEntityDamage(EntityDamageByEntityEvent $event){
        $config = new Config($this->main->getDataFolder() . "config.yml", Config::YAML);

        $killer = $event->getDamager();
        $victim = $event->getEntity();

        if($victim instanceof Player and $killer instanceof Player){
            if($event->getFinalDamage() > $victim->getHealth()){
                $armor = $config->get("Armor");
                $items = $config->get("Items");

                $victim->setHealth(20);
                $victim->removeAllEffects();
                $killedMessage = str_replace("{KILLER}", $killer->getNameTag(), $config->get("KilledMessage"));
                $killedMessage2 = str_replace("{VICTIM}", $victim->getNameTag(), $killedMessage);
                $killedMessage3 = str_replace("{LAST_HEALTH}", round($killer->getHealth() / 2, 1, 5), $killedMessage2);
                $victim->sendMessage($config->get("Prefix") . $killedMessage3);
                $victim->teleport($victim->getLevel()->getSafeSpawn());
                $victimconfig = new Config($this->main->getDataFolder() . "player/" . $victim->getName() . ".yml", Config::YAML);
                $victimconfig->set("Deaths", $victimconfig->get("Deaths") + 1);
                $victimconfig->set("Streak", 0);
                $victimconfig->save();
                $victim->getInventory()->clearAll();
                $victim->getArmorInventory()->clearAll();
                $victim->getArmorInventory()->setHelmet(Item::get($armor[0]));
                $victim->getArmorInventory()->setChestplate(Item::get($armor[1]));
                $victim->getArmorInventory()->setLeggings(Item::get($armor[2]));
                $victim->getArmorInventory()->setBoots(Item::get($armor[3]));
                for($i = 0; $i < count($items); $i++) {
                    $itemArray = $items[$i];

                    $victim->getInventory()->addItem(Item::get($itemArray[0], $itemArray[1], $itemArray[2]));
                }

                $newHealth = $killer->getHealth() + 6;
                $killer->setHealth($newHealth);
                $killMessage = str_replace("{KILLER}", $killer->getNameTag(), $config->get("KillMessage"));
                $killMessage2 = str_replace("{VICTIM}", $victim->getNameTag(), $killMessage);
                $killer->sendMessage($config->get("Prefix") . $killMessage2);
                $killerconfig = new Config($this->main->getDataFolder() . "player/" . $killer->getName() . ".yml", Config::YAML);
                $killerconfig->set("Kills", $killerconfig->get("Kills") + 1);
                $killerconfig->set("Streak", $killerconfig->get("Streak") + 1);
                $killerconfig->save();

                if($killerconfig->get("Streak") === 5){
                    $this->main->getServer()->broadcastMessage($config->get("Prefix") . TextFormat::YELLOW . $killer->getNameTag() . "§a has a killstreak of §c5§a!");
                }

                $killer->getInventory()->clearAll();
                $killer->getArmorInventory()->clearAll();
                $killer->getArmorInventory()->setHelmet(Item::get($armor[0]));
                $killer->getArmorInventory()->setChestplate(Item::get($armor[1]));
                $killer->getArmorInventory()->setLeggings(Item::get($armor[2]));
                $killer->getArmorInventory()->setBoots(Item::get($armor[3]));
                for($i = 0; $i < count($items); $i++) {
                    $itemArray = $items[$i];

                    $killer->getInventory()->addItem(Item::get($itemArray[0], $itemArray[1], $itemArray[2]));
                }
            }
        }
    }

    public function onDeath(PlayerDeathEvent $event){
        $config = new Config($this->main->getDataFolder() . "config.yml", Config::YAML);

        $event->setDrops(array(Item::get(Item::AIR)));

        $victimconfig = new Config($this->main->getDataFolder() . "player/" . $event->getPlayer()->getName() . ".yml", Config::YAML);
        $event->setDeathMessage("");
        $event->getPlayer()->sendMessage($config->get("Prefix") . $config->get("DeathMessage"));
        $victimconfig->set("Deaths", $victimconfig->get("Deaths") + 1);
        $victimconfig->save();
    }

    public function onRespawn(PlayerRespawnEvent $event){
        $config = new Config($this->main->getDataFolder() . "config.yml", Config::YAML);

        $armor = $config->get("Armor");
        $items = $config->get("Items");
        $event->getPlayer()->getArmorInventory()->setHelmet(Item::get($armor[0]));
        $event->getPlayer()->getArmorInventory()->setChestplate(Item::get($armor[1]));
        $event->getPlayer()->getArmorInventory()->setLeggings(Item::get($armor[2]));
        $event->getPlayer()->getArmorInventory()->setBoots(Item::get($armor[3]));

        for($i = 0; $i < count($items); $i++) {
            $itemArray = $items[$i];

            $event->getPlayer()->getInventory()->addItem(Item::get($itemArray[0], $itemArray[1], $itemArray[2]));
        }

    }

}
