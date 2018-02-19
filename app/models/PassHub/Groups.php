<?php

namespace PassHub;

/**
 * Groups class
 *
 * Allows permitted users to manage groups.
 * Each group has custom access permissions for Pages and Categories.
 *
 * @package PassHub
 * @version 1.1.0
 * @author Derek Loewen <derek@derekloewen.com>
 * @copyright 2016 Derek Loewen
 */

class Groups extends Model
{
    /**
     * ID of this model's corresponding entry in the 'pages' database table
     * @var integer
     */
    protected $page_id = 4;

    /**
     * Call parent constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show groups page.
     */
    public function view()
    {
        // Does the user have access to this resource?
        $this->showErrorIfDenied('page', $this->page_id);

        $this->f3->set('mode', 'groups');
        $this->f3->set('content', 'groups.html.php');
        echo \Template::instance()->render('base.html.php');
    }

    /**
     * Output list of groups, pages, and categories in JSON format
     * Use with AJAX request
     */
    public function get()
    {
        // Does the user have access to this resource?
        $this->exitWithErrorIfDenied('page', $this->page_id, ACL_READ);

        // Verify CSRF token
        if (!$this->verifyCSRF()) {
            header('HTTP/1.0 402 Request Failed');
            exit;
        }

        // Get groups data
        // ------------------------------

        /*
        Select all groups rows except for the admin
        This prevents the admin from being deleted
        and locking out all access from the system.
        */
        $groups = $this->getGroups(true);

        if (false === count($groups) > 0) {
            header('HTTP/1.0 402 Request Failed');
        }

        // Get pages data
        // ------------------------------

        $pages = $this->getPages();

        // Get categories data
        // ------------------------------

        // Get without ACL, hide private categories
        $categories = $this->getCategories(false, false);

        // Get permissions data
        // ------------------------------

        $permissions = $this->getPermissions();

        // Output all data in JSON format
        // ------------------------------

        $this->f3->set('data', array());
        $this->f3->set('data.groups', $groups);
        $this->f3->set('data.pages', $pages);
        $this->f3->set('data.categories', $categories);
        $this->f3->set('data.permissions', $permissions);

        header('HTTP/1.0 200 OK');

        echo utf8_encode(json_encode($this->f3->get('data')));
    }

    /**
     * Save group data to database
     * Use with AJAX request
     */
    public function post()
    {
        $success = true;

        // Verify CSRF token
        if (!$this->verifyCSRF()) {
            header('HTTP/1.0 402 Request Failed');
            exit;
        }

        // Add/edit database rows

        // Get POST data
        $post               = $this->f3->get('POST');
        $groupsToAdd        = $post['saveData']['groups']['add'];
        $groupsToDelete     = $post['saveData']['groups']['delete'];
        $groupPermissions   = $post['saveData']['groupPermissions'];

        /* 1. Add Groups - in order to save permissions for new groups, the group must be added first. */
        if (!empty($groupsToAdd)) {
            // Check permission to create
            $this->exitWithErrorIfDenied('page', $this->page_id, ACL_CREATE);
            foreach ($groupsToAdd as $group) {
                $name = trim($group['name']);
                // Insert into database
                $this->groups->name = $name;
                // Save & reset
                if (!$this->groups->save()) {
                    header('HTTP/1.0 402 Request Failed');
                    echo "Failed to add group with name '$name'.";
                    exit;
                } else {
                    // Set ID for the new group in groupPermissions
                    if (!empty($groupPermissions)) {
                        // Find the right group to add to
                        foreach ($groupPermissions as $key => $groupPermission) {
                            if ($groupPermission['name'] === $name) {
                                $groupPermissions[$key]['id'] = $this->groups->id;
                                break;
                            }
                        }
                    }
                }
                $this->groups->reset();
            }
        }

        /* 3. Set permissions for groups & update the group name - now that new groups are added and old ones deleted, permissions can be stored */
        if (!empty($groupPermissions)) {
            foreach ($groupPermissions as $group) {
                // Skip this loop if the group was deleted
                if (
                    ! empty($groupsToDelete)
                    && in_array($group['id'], $groupsToDelete) === true
                ) {
                    continue;
                }
                // Update the group name if it already exists in the database
                // and has changed
                if (
                    intval($group['id']) > 0
                    && $this->groups->load(['id = ?', intval($group['id'])])
                    && $this->groups->name != $group['name']
                ) {
                    // Check permission to edit
                    $this->exitWithErrorIfDenied('page', $this->page_id, ACL_EDIT);
                    // Update name
                    $this->groups->name = $group['name'];
                    $this->groups->save();
                    $this->groups->reset();
                }

                // Store permissions for each type
                // -------------------------------
                if (!empty($group['types'])) {
                    // Check permission to edit
                    $this->exitWithErrorIfDenied('page', $this->page_id, ACL_EDIT);
                    foreach ($group['types'] as $type => $resource) {
                        // $type could be pages or categories
                        // $resource have the ID as the key, and optional read, create, update, and delete permissions set to true or false
                        // Loop through resource's permissions
                        foreach ($resource as $resource_id => $permissions) {
                            foreach ($permissions as $perm_name => $perm_value) {
                                // Insert or update permission in database

                                // Convert perm_value to boolean
                                $perm_value = filter_var($perm_value, FILTER_VALIDATE_BOOLEAN);

                                // Get access level number from permission name
                                $access_level_num = $this->acl->getAccessLevelNumber($perm_name);
                                
                                // Load existing database row if available
                                $acl_result = $this->acl->load([
                                    'groupId = ? and type = ? and foreignId = ? and accessLevel = ?',
                                    intval($group['id']),
                                    $type,
                                    intval($resource_id),
                                    $access_level_num
                                ]);

                                // If database row exists and permission was marked false, remove the row
                                if (
                                    $acl_result->id > 0
                                    && $perm_value === false
                                ) {
                                    // Erase row and reset for the next loop iteration
                                    $this->acl->erase();
                                    $this->acl->reset();
                                }

                                // If no database row exists for for the permission, and it's true, add a new row
                                if (
                                    is_null($acl_result->id)
                                    && $perm_value === true
                                ) {
                                    // Set row values
                                    $this->acl->groupId     = $group['id'];
                                    $this->acl->type        = $type;
                                    $this->acl->foreignId   = intval($resource_id);
                                    $this->acl->accessLevel = $access_level_num;
                                    // Save row and reset for the next loop iteration
                                    $this->acl->save();
                                    $this->acl->reset();
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($success) {
            header('HTTP/1.0 200 OK');
        } else {
            header('HTTP/1.0 402 Request Failed');
        }
    }

    /**
     * Delete groups from database by ID
     * Use with AJAX request
     *
     * Permissions related to each group are automatically deleted when the group is deleted
     * That's handled with a foreign key constraint in MySQL
     */
    public function delete()
    {
        $success = false;

        // Does the user have access to this resource?
        $this->exitWithErrorIfDenied('page', $this->page_id, ACL_DELETE);

        // Verify CSRF token
        if (!$this->verifyCSRF()) {
            header('HTTP/1.0 402 Request Failed');
            exit;
        }

        $groupIdsString = '';

        // Delete each group
        if ($this->f3->exists('POST.deleteItemsQueue')) {
            $groupIds = $this->f3->get('POST.deleteItemsQueue');
            foreach ($groupIds as $id) {
                // Validate ID
                if (intval($id) > 0) {
                    // build comma separated string for later
                    $groupIdsString .= intval($id).', ';
                    // Load and delete each category
                    $this->groups->load(array('id = ?', intval($id)));
                    if ($this->groups->erase()) {
                        $success = true;
                    }
                    $this->groups->reset();
                }
            }
        }

        if ($success) {
            header('HTTP/1.0 200 OK');
        } else {
            header('HTTP/1.0 402 Request Failed');
        }
    }
}
