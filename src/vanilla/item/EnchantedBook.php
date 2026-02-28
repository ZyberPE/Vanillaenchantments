<?php

declare(strict_types=1);

namespace vanilla\item;

use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;

class EnchantedBook extends Item{

    public function __construct(){
        parent::__construct(
            new ItemIdentifier(ItemTypeIds::ENCHANTED_BOOK),
            "Enchanted Book"
        );
    }

    public function getMaxStackSize() : int{
        return 1;
    }
}
