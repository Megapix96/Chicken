<?php
namespace Megapix96;

use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\MoveEntityPacket;
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
          	$flags |= 1 << 14;
          	$flags |= 1 << 15;
          	$flags |= 1 << 16;
        	$pk->metadata = [
			38 => [7, -1],
			24 => [0, 0],
			4 => [4, $n],
			0 => [7, $flags]
		];
        	$this->getServer()->broadcastPacket($this->getServer()->getOnlinePlayers(), $pk);
        	foreach ($this->getServer()->getOnlinePlayers() as $pl){
        		if ($pl->getName() !== $n){
	        		$pk = new AddEntityPacket();
	        		$pk->eid = $this->eid[$pl->getName()];
	        		$pk->type = self::NETWORK_ID;
	        		$pk->x = $p->x;
	        		$pk->y = $p->y;
	        		$pk->z = $p->z;
	        		$flags = 0;
	          		$flags |= 1 << 14;
	          		$flags |= 1 << 15;
	          		$flags |= 1 << 16;
	        		$pk->metadata = [
					38 => [7, -1],
					24 => [0, 0],
					4 => [4, $pl->getName()],
					0 => [7, $flags]
				];
				$p->dataPacket($pk);
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
        	$this->getServer()->broadcastPacket($this->getServer()->getOnlinePlayers(), $pk);
        }
}