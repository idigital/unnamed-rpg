<?php

/**
* Gets the data a particular coord on the map
*/

class MapGrid extends StandardObject {
	public function __construct ($grid_id) {
		//  We'll need a database.. Fortunately, constants stick around even inside private scope!
		$Database = new Database (database_server, database_user, database_password, database_name);
	
		$config = array (
			'table' => "map",
			'database' => $Database,
			'item_id' => $grid_id,
			'primary_key' => "grid_id"
		);
		
		parent::__construct ($config);
	}
	
	/**
	* Based on what square we're looking at, what are the chances of a monster spawning?
	*
	* Check out the map.draw.php file to see the meanings of each type.
	*
	* @return int Percentile chance (100 = 100%) of a mob spawning.
	*/
	public function getSpawnChance (Character $Char = null) {
		switch ($this->getDetail ('type')) {
			case 1:
				$chance = 20;
				break;
			case 2:
				$chance = 30;
				break;
			case 3:
				// unused
				$chance = 100;
				break;
			case 4:
				// don't spawn on special coords
				$chance = -100;
				break;
			case 5:
				// not even possible for them to be here
				$chance = 1000;
				break;
			case 6:
				$chance = 5;
				break;
			default:
				$chance = 1000;
		}
		
		// if there's a character set, their current movement method will have to be taken into account
		if (is_object ($Char)) {
			switch ($Char->getMapData()->getDetail ('move_type')) {
				case 'normal':
					// nothing happens
					break;
				case 'sneak':
					// if they're sneaking in a peaceful area, they'll never encounter a mob
					$chance -= 5;
					break;
				case 'hunt':
					$chance += 15;
					break;
				default:
					// nothing
			}
			
		}

		return (int) $chance;
	}
	
	/**
	* Gets a MapGrid object via it's coords, rather than grid_id.
	*
	* @param int
	* @param int
	* @param int
	* @return MapGrid
	*/
	public static function byCoord ($map_id, $x, $y) {
		$Database = new Database (database_server, database_user, database_password, database_name);
		
		$grid_id = $Database->getSingleValue ("SELECT `grid_id` FROM `map` WHERE `map_id` = ".(int) $map_id." AND `x_co` = ".(int) $x." AND `y_co` = ".(int) $y);
		
		return new self ($grid_id);
	}
}

?>