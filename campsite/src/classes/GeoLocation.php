<?php
/**
 * @package Campsite
 */

/**
 * Includes
 */
require_once($GLOBALS['g_campsiteDir'].'/classes/DatabaseObject.php');
require_once($GLOBALS['g_campsiteDir'].'/classes/SQLSelectClause.php');
//require_once($GLOBALS['g_campsiteDir'].'/classes/CampCacheList.php');
//require_once($GLOBALS['g_campsiteDir'].'/template_engine/classes/CampTemplate.php');

require_once($GLOBALS['g_campsiteDir'].'/classes/GeoLocationContent.php');
require_once($GLOBALS['g_campsiteDir'].'/classes/GeoMultimedia.php');

/**
 * @package Campsite
 */
class Geo_Location extends DatabaseObject {
	var $m_keyColumnNames = array('id');
	var $m_dbTableName = 'LocationContents';
	//var $m_columnNames = array('id', 'city_id', 'city_type', 'population', 'position', 'latitude', 'longitude', 'elevation', 'country_code', 'time_zone', 'modified');

	/**
	 * The geo location contents class is for load/store of POI data.
	 */
	public function Geo_Location()
	{
	} // constructor


	/**
	 * Finds POIs on given article and language
	 *
	 * @param string $p_articleNumber
	 * @param string $p_languageId
	 *
	 * @return array
	 */
/*
	public static function ReadArticlePoints($p_articleNumber, $p_languageId)
	{
		global $g_ado_db;
		$sql_params = array($p_articleNumber, $p_languageId);

	} // fn ReadArticlePoints
*/

    // NOTE: the 'location' ('center') parameters should be array with points (a point) with lat/lon values
    public static function FindLocation($p_location, $p_type, $p_style, $p_center, $p_radius)
    {
		global $g_ado_db;

        if ("point" != $p_type) {return null;}

        $queryStr_point = "SELECT id FROM Locations WHERE poi_location = GeomFromText('POINT(? ?)') AND poi_type = 'point' ";
        $queryStr_point .= "AND poi_type_style= ? AND poi_center = PointFromText('POINT(? ?)') AND poi_radius = ?";

        $loc_id = 0;

        // here checking for points; nothing else yet
        if ("point" == $p_type)
        {
            try
            {
                $loc_latitude = $p_location[0]['latitude'];
                $loc_longitude = $p_location[0]['longitude'];
                $cen_latitude = $p_center['latitude'];
                $cen_longitude = $p_center['longitude'];

                $sql_params = array();
    
                $sql_params[] = "" . $loc_latitude;
                $sql_params[] = "" . $loc_longitude;
                $sql_params[] = "" . $p_style;
                $sql_params[] = "" . $cen_latitude;
                $sql_params[] = "" . $cen_longitude;
                $sql_params[] = 0 + $p_radius;
    
                //$queryStr = str_replace("%%location%%", $p_location, $queryStr);
                //$queryStr = str_replace("%%center%%", $p_center, $queryStr);
    
                $rows = $g_ado_db->GetAll($queryStr_point, $sql_params);
                if (is_array($rows)) {
                    foreach ($rows as $row) {
                        $loc_id = $row['id'];
                    }
                }
            }
            catch (Exception $exc)
            {
                return false;
            }
        }

        return $loc_id;
    }

	public static function UpdateLocations($p_mapId, $p_locations)
    {
		global $g_ado_db;
        global $g_user;

/*
    A)
        1) given article_number, language_id, map_id, list of map_loc_id / new locations

    B)
        cycle:
            1) read location_id (as old_loc_id) of the map_loc_id
            2) insert new location with new positions
            3) get the inserted id into new_loc_id
            4) update maplocations into the new_loc_id for the map_loc_id
            6) delete location of old_loc_id if none maplocation with a link into the old_loc_id

*/

        // ad B 1)
        $queryStr_loc_id = "SELECT fk_location_id AS loc FROM MapLocations WHERE id = ?";
        // ad B 2)
		$queryStr_loc_in = "INSERT INTO Locations (poi_location, poi_type, poi_type_style, poi_center, poi_radius, IdUser) VALUES (";
        $queryStr_loc_in .= "GeomFromText('POINT(? ?)'), 'point', 0, PointFromText('POINT(? ?)'), 0, %%user_id%%";
        $queryStr_loc_in .= ")";
        // ad B 4)
        $queryStr_map_up = "UPDATE MapLocations SET fk_location_id = ? WHERE id = ?";
        // ad B 6)
        $queryStr_loc_rm = "DELETE FROM Locations WHERE id = ? AND NOT EXISTS (SELECT id FROM MapLocations WHERE fk_location_id = ?)";

        // updating current POIs, inserting new POIs
        foreach ($p_locations as $poi_obj)
        {
            $poi = get_object_vars($poi_obj);

            // ad B 1)
            $loc_old_id = null;
            try
            {
                $maploc_sel_params = array();
                $maploc_sel_params[] = $poi["id"];

                $rows = $g_ado_db->GetAll($queryStr_loc_id, $maploc_sel_params);
                if (is_array($rows)) {
                    foreach ($rows as $row) {
                        $loc_old_id = $row['loc'];
                    }
                }
            }
            catch (Exception $exc)
            {
                return false;
            }

            if (null === $loc_old_id) {continue;}

            $loc_new_id = null;

            $new_loc = array();
            $new_loc[] = array('latitude' => $poi["latitude"], 'longitude' => $poi["longitude"]);
            $new_cen = array('latitude' => $poi["latitude"], 'longitude' => $poi["longitude"]);
            $new_style = 0;
            $new_radius = 0;
            $reuse_id = Geo_Location::FindLocation($new_loc, 'point', $new_style, $new_cen, $new_radius);

            if ($reuse_id && (0 < $reuse_id))
            {
                $loc_new_id = $reuse_id;
            }
            else
            {
                // ad B 2)
                {
                    $loc_in_params = array();
                    $loc_in_params[] = $poi["latitude"];
                    $loc_in_params[] = $poi["longitude"];
                    $loc_in_params[] = $poi["latitude"];
                    $loc_in_params[] = $poi["longitude"];
    
                    $queryStr_loc_in = str_replace("%%user_id%%", $g_user->getUserId(), $queryStr_loc_in);

                    $success = $g_ado_db->Execute($queryStr_loc_in, $loc_in_params);
                }
    
                // ad B 3)
                // taking its ID for the next processing
                $loc_new_id = $g_ado_db->Insert_ID();
            }

            // ad B 4)
            {
                $map_up_params = array();
                $map_up_params[] = $loc_new_id;
                $map_up_params[] = $poi["id"];

                $success = $g_ado_db->Execute($queryStr_map_up, $map_up_params);
            }

            // ad B 6)
            try
            {
                $loc_rm_params = array();
                $loc_rm_params[] = $loc_old_id;
                $loc_rm_params[] = $loc_old_id;

                $success = $g_ado_db->Execute($queryStr_loc_rm, $loc_rm_params);
            }
            catch (Exception $exc)
            {
                return false;
            }

        }

        ;
        return true;
    }

    public static function UpdateIcon($poi)
    {
		global $g_ado_db;

        $queryStr = "UPDATE MapLocations SET poi_style = ? WHERE id = ?";

        $sql_params = array();
        $sql_params[] = $poi["style"];
        $sql_params[] = $poi["location_id"];

        $success = $g_ado_db->Execute($queryStr, $sql_params);
    }

	public static function UpdateContents($p_mapId, $p_contents)
    {
		global $g_ado_db;

        foreach ($p_contents as $poi_obj)
        {
            $poi = get_object_vars($poi_obj);

            if ($poi["icon_changed"])
            {
                Geo_Location::UpdateIcon($poi);
            }
            if ($poi["state_changed"])
            {
                Geo_LocationContent::UpdateState($poi);
            }
            if ($poi["image_changed"])
            {
                Geo_Multimedia::UpdateMedia($poi, "image");
            }
            if ($poi["video_changed"])
            {
                Geo_Multimedia::UpdateMedia($poi, "video");
            }

            //if (!$poi["text_changed"]) {continue;}
            if ($poi["text_changed"])
            {
                Geo_LocationContent::UpdateText($poi);
            }

        }

        ;
        return true;
    }

	public static function UpdateOrder($p_mapId, $p_reorder, $p_indices)
    {
		global $g_ado_db;

/*
    A)
        1) given article_number, language_id, map_id, list of content_ids with new contents

    B)
        cycle:
            point order is shared between langages
                1 a) read location_id of the content_id, if an old point
                1 b) or get location_id from the new indices for new points
                2) update rank on map_id/location_id

*/

        // ad B 2)
        $queryStr_rnk_up = "UPDATE MapLocations SET rank = ? WHERE id = ?";

        $rank = 0;
        foreach ($p_reorder as $poi_obj)
        {
            $rank += 1;
            $db_id = 0;

            $poi = get_object_vars($poi_obj);

            try
            {
                $state = $poi['state'];
                if ('new' == $state)
                {
                    // ad B 1 b)
                    $tmp_key = $poi['index'];
                    $db_id = $p_indices[$tmp_key]['maploc'];
                }
                else
                {
                    //ad B 1 a)
                    $db_id = 0 + $poi['location'];

                }

                // ad B 2)
                {
                    $rnk_up_params = array();
                    $rnk_up_params[] = $rank;

                    $rnk_up_params[] = $db_id;


                    $success = $g_ado_db->Execute($queryStr_rnk_up, $rnk_up_params);
                }
            }
            catch (Exception $exc)
            {
                return false;
            }

        }

        return true;
    }

} // class Geo_LocationContents

?>