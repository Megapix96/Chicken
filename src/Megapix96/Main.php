<?php
namespace Megapix96;

use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\MoveEntityPacket;
use pocketmine\network\protocol\RemoveEntityPacket;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener{

	const NETWORK_ID = 10;

        function onEnable(){
                $this->getServer()->getPluginManager()->registerEvents($this,$this);
                $this->eid = [];
        }

        function onJoin(PlayerJoinEvent $ev){
        	$p = $ev->getPlayer();
        	$n = $p->getName();
        	$p->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_INVISIBLE, 1);
        	if (empty($this->eid[$n])) $this->eid[$n] = Entity::$entityCount++;
        	$pk = new AddEntityPacket();
        	$pk->eid = $this->eid[$n];
        	$pk->type = self::NETWORK_ID;
        	$pk->x = $p->x;
        	$pk->y = $p->y;
        	$pk->z = $p->z;
        	$flags = 0;
          	$flags |= 1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG;
          	$flags |= 1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG;
          	$flags |= 1 << Entity::DATA_FLAG_IMMOBILE;
        	$pk->metadata = [
			Entity::DATA_LEAD_HOLDER => [Entity::DATA_TYPE_SHORT, -1],
			Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $n],
			Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags]
		];

		foreach ($p->getLevel()->getPlayers() as $pl){
        		if ($pl->getName() !== $n){
				$pl->dataPacket($pk);
			}
		}
        }

        function onMove(PlayerMoveEvent $ev){
        	$p = $ev->getPlayer();
        	$n = $p->getName();
        	$pk = new MoveEntityPacket();
        	$pk->eid = $this->eid[$n];
        	$pk->x = $p->x;
        	$pk->y = $p->y;
        	$pk->z = $p->z;
        	$pk->yaw = $p->yaw;
        	$pk->pitch = $p->pitch;
        	foreach ($p->getLevel()->getPlayers() as $pl){
        		$pl->dataPacket($pk);
        	}
        }

	function onLevelChange(EntityLevelChangeEvent $ev){
		$p = $ev->getEntity();
		$n = $p->getName();
		if ($p instanceof Player){
			foreach ($ev->getOrigin()->getPlayers() as $pl){
				if ($pl->getName() !== $n){
					$pk = new RemoveEntityPacket();
					$pk->eid = $this->eid[$n];
					$pl->dataPacket($pk);
				}
			}
			foreach ($ev->getTarget()->getPlayers() as $pl){
				if ($pl->getName() !== $n){
					$pk = new AddEntityPacket();
			        	$pk->eid = $this->eid[$n];
			        	$pk->type = self::NETWORK_ID;
			        	$pk->x = $p->x;
			        	$pk->y = $p->y;
			        	$pk->z = $p->z;
			        	$flags = 0;
			          	$flags |= 1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG;
			          	$flags |= 1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG;
			          	$flags |= 1 << Entity::DATA_FLAG_IMMOBILE;
			        	$pk->metadata = [
						Entity::DATA_LEAD_HOLDER => [Entity::DATA_TYPE_SHORT, -1],
						Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $n],
						Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags]
					];
					$pl->dataPacket($pk);
				}
			}
		}
	}
}