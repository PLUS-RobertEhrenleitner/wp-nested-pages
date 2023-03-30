<?php
namespace NestedPages\Entities\User;

use NestedPages\Config\SettingsRepository;

/**
* Register custom user roles
*/
class UserCapabilities
{
	/**
	* Settings Repository
	*/
	private $settings;

	public function __construct()
	{	
		$this->settings = new SettingsRepository;
		add_action('init', [$this, 'addSortingCapabilities']);
		add_action('init', [$this, 'addPageGroupCapabilities']);
	}

	/**
	* Adds custom capability of nestedpages_sort_$type 
	*/
	public function addSortingCapabilities()
	{
		$post_types = get_post_types(['public' => true]);
		$invalid = ['attachment'];
		$granted_roles = ['administrator'];
		$roles = wp_roles();
		foreach ( $post_types as $type ) :
			if ( in_array($type, $invalid) ) continue;
			foreach ( $roles->roles as $name => $role_obj ) :
				$role = get_role($name);
				$grant_capability = ( in_array($name, $granted_roles) ) ? true : false;
				if ( $role->has_cap("nestedpages_sorting_$type") ) $grant_capability = true;

				/**
				 * Filters the sorting capability for a given role and post type.
				 *
				 * @since 3.1.9
				 *
				 * @param bool  $grant_role     Whether role may sort post type.
				 * @param string $type 			The post type name.
				 * @param string  $role_name	The Role Name.
				 */
				$grant_capability = apply_filters("nestedpages_sorting_capability", $grant_capability, $type, $role);
				if ( $grant_capability ) $role->add_cap("nestedpages_sorting_$type", true);
			endforeach;
		endforeach;
	}

	/**
	* Adds page group capabilities
	*/
	public function addPageGroupCapabilities()
	{
		$actions = ['create', 'delete', 'edit'];
		$allowed_pagegroup_actions = [];
		$rolenames = array_keys(wp_get_current_user()->get_role_caps());
		foreach ( $actions as $action ) :
			$allowed_roles = get_option("nestedpages_pagegroup_$action");
			$user_allowed = array_intersect($allowed_roles, $rolenames);
			if ( count($user_allowed) <> 0 ) $allowed_pagegroup_actions[] = $action;
		endforeach;
		if ( !in_array('create', $allowed_pagegroup_actions) ) $this->preventPageGroupAction_Create('create'); // how to detect a creation?
		if ( !in_array('delete', $allowed_pagegroup_actions) ) $this->preventPageGroupAction_EditDelete('delete');
		if ( !in_array('edit', $allowed_pagegroup_actions) ) $this->preventPageGroupAction_EditDelete('edit');
	}

	private function preventPageGroupAction_Create() {
	}

	public function preventPageGroupAction_EditDelete($action) {
		add_filter('user_has_cap', function ($all_caps, $caps, $args, $wp_user) use ($action) {
			$revoke = false;
			if ( mb_ereg('^' . $action . '_.*posts?', $args[0]) && count($args) == 3 ) {
				$post_id = $args[2];
				$post = get_post($post_id); // room for performance improvement
				if ( $post )
					if ( !$post->post_parent ) $revoke = true;
			}
			return $revoke ? [] : $all_caps;
		}, 10, 4);
	}

}
