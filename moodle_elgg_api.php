<?php

/**
 * Get the guid of the group associated with the Moodle course.
 * Create a new one if group is not found.
 * 
 * @param $shortname string Shortname of the Moodle course
 * @return int GUID of the group 
 */
function api_moodle_integration_get_group_guid($shortname, $community) {
	global $CONFIG;

	// Authenticate user with REST API authentication token
	$token = get_input('auth_token');
	if ($user_guid = validate_user_token($token, $CONFIG->site_id)) {
		$user = get_user($user_guid);
	} else {
		throw new APIException(elgg_echo('APIException:GetGroupGuid:NoUser'));
	}

	$groupname = $shortname . '-' . $community;

	$options = array(
		'metadata_name' => 'moodle_shortname',
		'metadata_value' => $groupname,
		'type' => 'group',
		'limit' => 1
	);

	if ($groups = elgg_get_entities_from_metadata($options)) {
		// Take the first found group
		$group = $groups[0];

		if (!$group->isMember($user)) {
			$group->join($user);
		}

		// return the guid
		return $group->getGUID();
	} else {
		// Group was not found so let's create a new one
		$group = new ElggGroup();
		$group->membership = ACCESS_PRIVATE;
		$group->access_id = ACCESS_PUBLIC;
		$group->name = $groupname;
		$group->moodle_shortname = $shortname;
		$guid = $group->save();

		if ($guid) {
			$group->join($user);

			return $guid;
		} else {
			throw new APIException(elgg_echo('APIException:GetGroupGuid:UnableToCreateGroup'));
		}
	}
}

/**
 * Get latest discussions of the group.
 * 
 * Note that get only the discussions which have at least one reply.
 * 
 * @param int $group_guid
 * @return array $return Array of discussion items
 */
function api_moodle_integration_get_group_discussions($group_guid){
	$return = array();

	$options = array(
		'type' => 'object',
		'subtype' => 'groupforumtopic',
		'annotation_name' => 'group_topic_post',
		'container_guid' => $group_guid,
		'limit' => 5
	);

	if ($forum = elgg_get_entities_from_annotations($options)) {
		foreach($forum as $message){
			$return[] = array(
				'title' => $message->title, 
				'url' => $message->getUrl(), 
				'time' => elgg_get_friendly_time($message->time_created),
				'user' => $message->getOwnerEntity()->name
			);
		}
	}

	return $return;
}

/**
 * Get any Elgg objects tagged with the shortname of Moodle course.
 * 
 * @param string $object_type Sybtype of the objecy (file, blog, messages)
 * @param string $tag Moodle course shortname
 * @return array $return Array of object information
 */
function api_moodle_integration_get_objects($object_type, $tag){
	$return = array();

	$options = array(
		'metadata_name' => 'tags',
		'metadata_value' => $tag,
		'type' => 'object',
		'subtype' => $object_type,
		'metadata_case_sensitive' => false,
	);

	$objects = elgg_get_entities_from_metadata($options);

	if ($objects) {
		foreach($objects as $object) {
			$return[] = array(
				'title' => $object->title,
				'url' => $object->getURL(),
				'time' => elgg_get_friendly_time($object->time_created),
				'user' => $object->getOwnerEntity()->name
			);
		}
	}

	return $return;
}

function produce_login_token() {
	global $CONFIG;

	// get the user guid from the auth token
	$auth_token = get_input('auth_token');
	$user_guid = validate_user_token($auth_token, $CONFIG->site_id);

	// produce the login token
	$login_token = "";
	for($i = 0; $i < 4; $i++) {
		$num = mt_rand(0, 0xffff);
		$output = sprintf("%04x", $num);
		$login_token .= $output;
	}

	// save it with a timeout and the associated guid
	$token_data = new ElggObject();
	$token_data->title = "Token data";
	$token_data->description = "";
	$token_data->owner_guid = 0;
	$token_data->container_guid = 0;
	$token_data->subtype = "elgg-moodle-token-data";
	$token_data->access_id = ACCESS_PUBLIC;
	$token_data->token = $login_token;
	$token_data->user_guid = $user_guid;
	$token_data->timeout = time() + 10;

	$token_data->save();

	return $login_token;
}
