<?php

declare(strict_types=1);

namespace vanilla;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

use pocketmine\player\Player;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityShootBowEvent;

use pocketmine\entity\Living;
use pocketmine\entity\EntityTypeIds;

use pocketmine\block\BlockTypeIds;

use pocketmine\item\VanillaItems;
use pocketmine\item\enchantment\VanillaEnchantments;

class Core extends PluginBase implements Listener{

    public function onEnable() : void{
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info("VanillaEnchantments enabled (API 5)");
    }

    /* ------------------------------------------------ */
    /*                     FORTUNE                     */
    /* ------------------------------------------------ */

    public function onBreak(BlockBreakEvent $event) : void{
        $block = $event->getBlock();
        $item = $event->getItem();

        // Apple drop from oak leaves (10%)
        if($block->getTypeId() === BlockTypeIds::OAK_LEAVES){
            if(mt_rand(1, 100) <= 10){
                $event->setDrops([VanillaItems::APPLE()]);
            }
        }

        $fortuneLevel = $item->getEnchantmentLevel(VanillaEnchantments::FORTUNE());

        if($fortuneLevel > 0){
            $add = mt_rand(0, $fortuneLevel + 1);

            $drops = $event->getDrops();
            foreach($drops as $drop){
                $drop->setCount($drop->getCount() + $add);
            }

            $event->setDrops($drops);
        }
    }

    /* ------------------------------------------------ */
    /*              SMITE / BANE / LOOTING             */
    /* ------------------------------------------------ */

    public function onDamage(EntityDamageByEntityEvent $event) : void{
        $victim = $event->getEntity();
        $damager = $event->getDamager();

        if(!$damager instanceof Player){
            return;
        }

        $item = $damager->getInventory()->getItemInHand();

        /* ---------- SMITE ---------- */
        $smiteLevel = $item->getEnchantmentLevel(VanillaEnchantments::SMITE());

        if($smiteLevel > 0){
            if(in_array($victim->getTypeId(), [
                EntityTypeIds::ZOMBIE,
                EntityTypeIds::SKELETON,
                EntityTypeIds::WITHER,
                EntityTypeIds::WITHER_SKELETON,
                EntityTypeIds::HUSK,
                EntityTypeIds::ZOMBIE_VILLAGER
            ])){
                $event->setBaseDamage(
                    $event->getBaseDamage() + (2.5 * $smiteLevel)
                );
            }
        }

        /* ---------- BANE OF ARTHROPODS ---------- */
        $baneLevel = $item->getEnchantmentLevel(VanillaEnchantments::BANE_OF_ARTHROPODS());

        if($baneLevel > 0){
            if(in_array($victim->getTypeId(), [
                EntityTypeIds::SPIDER,
                EntityTypeIds::CAVE_SPIDER,
                EntityTypeIds::SILVERFISH,
                EntityTypeIds::ENDERMITE
            ])){
                $event->setBaseDamage(
                    $event->getBaseDamage() + (2.5 * $baneLevel)
                );
            }
        }

        /* ---------- LOOTING ---------- */
        $lootingLevel = $item->getEnchantmentLevel(VanillaEnchantments::LOOTING());

        if(
            $lootingLevel > 0 &&
            $victim instanceof Living &&
            !$victim instanceof Player &&
            $event->getFinalDamage() >= $victim->getHealth()
        ){
            $add = mt_rand(0, $lootingLevel + 1);

            foreach($victim->getDrops() as $drop){
                $drop->setCount($drop->getCount() + $add);
                $victim->getWorld()->dropItem($victim->getPosition(), $drop);
            }
        }
    }

    /* ------------------------------------------------ */
    /*                  FAST ARROWS                    */
    /* ------------------------------------------------ */

    public function onShoot(EntityShootBowEvent $event) : void{
        $projectile = $event->getProjectile();

        if($projectile !== null && $projectile->getTypeId() === EntityTypeIds::ARROW){
            $event->setForce($event->getForce() + 0.95);
        }
    }
}
