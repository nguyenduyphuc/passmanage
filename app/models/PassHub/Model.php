<?php

namespace PassHub;

/**
 * Model base class
 *
 * Instantiates F3, database mappers, ACL, and SESSION.
 * Verifies user is logged in or is in the process of logging in.
 * Provides helper functions used with other classes in this namespace.
 *
 * @package PassHub
 * @version 1.1.0
 * @author Derek Loewen <derek@derekloewen.com>
 * @copyright 2016 Derek Loewen
 */

class Model
{
    /**
     * Fat-free Framework Instance
     * @var object
     */
    protected $f3;

    /**
     * ACL class instance
     * @var object
     */
    protected $acl;

    /**
     * Database connection
     * @var object
     */
    protected $db;

    /**
     * Logins Database ORM instance
     * @var object
     */
    protected $logins;

    /**
     * Fields Database ORM instance
     * @var object
     */
    protected $fields;
    
    /**
     * Categories Database ORM instance
     * @var object
     */
    protected $categories;

    /**
     * Groups Database ORM instance
     * @var object
     */
    protected $groups;

    /**
     * Users Database ORM instance
     * @var object
     */
    protected $users;

    /**
     * ID of the Administrator group
     * @var integer
     */
    protected $admin_group_id = 1;

    /**
     * The current user's private "My Logins" category ID
     * @var integer
     */
    protected $user_private_category_id = 1;

    /**
     * Sets up F3 instance, initializes database mappers, ACL, and SESSION.
     * Also verifies user is logged in or is in the process of logging in.
     */
    public function __construct()
    {
        // Main F3 instance
        $this->f3 = \Base::instance();

        // If database config is not set, trigger installer
        if (
            !$this->f3->get('DBHOST')
            || !$this->f3->get('DBPORT')
            || !$this->f3->get('DBNAME')
            || !$this->f3->get('DBUSER')
            || !$this->f3->get('DBPASS')
        ) {
            $this->f3->reroute('@installer');
        }

        // Set globally available variables
        $this->f3->set('BASEURL', $this->f3->get('SCHEME').'://'.$this->f3->get('HOST').':'.$this->f3->get('PORT').$this->f3->get('BASE'));

        // Set up blank error/success message variables
        $this->f3->set('success', '');
        $this->f3->set('error', '');

        // Database connection
        $this->db = new \DB\SQL('mysql:host='.$this->f3->get('DBHOST').';port='.$this->f3->get('DBPORT').';dbname='.$this->f3->get('DBNAME'), $this->f3->get('DBUSER'), $this->f3->get('DBPASS'));

        // Store Pages for use in base.html.php template
        $this->f3->set('pages', $this->getPages());

        // Database mappers
        $this->logins       = new \DB\SQL\Mapper($this->db, 'logins');
        $this->fields       = new \DB\SQL\Mapper($this->db, 'fields');
        $this->categories   = new \DB\SQL\Mapper($this->db, 'categories');
        $this->groups       = new \DB\SQL\Mapper($this->db, 'groups');
        $this->users        = new \DB\SQL\Mapper($this->db, 'users');

        // Session
        $session = new \DB\SQL\Session($this->db, 'sessions', true, function ($session) {
            // log suspect sessions to file
            $logger = new \Log('session.log');
            if (($ip = $session->ip()) != $this->f3->get('IP')) {
                $logger->write('user changed IP:'.$ip);
            } else {
                $logger->write('user changed browser/device:'.$this->f3->get('AGENT'));
            }
        });

        // Make CSRF token available in session
        // Create one if not already present
        if (
            !$this->f3->exists('SESSION.csrf')
            || $this->f3->get('SESSION.csrf') == ''
        ) {
            $this->f3->set('SESSION.csrf', $session->csrf());
        }

        // Set session cookies to expire in 30 days
        $this->f3->set('JAR.expire', time() + 60 * 60 * 24 * 30);

        // Refresh the user's group ID
        $this->f3->set('SESSION.group_id', $this->getUserGroupId());

        //var_dump($this->f3->get('SESSION.group_id')); exit;

        // Instantiate ACL
        $this->acl = new \ACL($this->db);
        $this->storeCurrentUserPermissions($this->acl);

        // Store user's private category ID
        $this->user_private_category_id = $this->getUserPrivateCategoryId();

        /*
        Logged in? If not, redirect to the login page
        IMPORTANT: Only perform this check if not accessing the auth pages,
        otherwise there will be a redirect loop
        */
        if (
            $this->f3->get('ALIAS') != 'auth'
            && $this->f3->get('ALIAS') != 'authpost'
            && $this->f3->get('ALIAS') != 'authreset'
            && $this->f3->get('ALIAS') != 'authresetsend'
            && $this->f3->get('ALIAS') != 'authresetkey'
            && $this->f3->get('ALIAS') != 'logout'
        ) {
            if (
                !$this->f3->exists('SESSION.logged_in')
                || intval($this->f3->get('SESSION.logged_in')) == 0
            ) {
                $this->f3->reroute('@auth');
            }
        }
    }

    /**
     * Show an error template if the user's group does not have permission to the specified resource
     *
     * @param string    $type 'page' or 'category'
     * @param int       $id the resource's ID
     * @param constant  $access_level see the ACL class for supported constants
     * @param boolean   $echotemplate true to show error message template if permission is denied
     *
     * @return bool true if permission is granted, false if not
     */
    public function showErrorIfDenied($type = '', $id = 0, $access_level = ACL_READ, $echotemplate = true)
    {
        if (
            $this->isAdmin() === false
            && $this->acl->check($type, $id, $access_level) === false
        ) {
            if ($echotemplate) {
                $this->f3->set('content', 'permissions.html.php');
                echo \Template::instance()->render('base.html.php');
                exit;
            }
            return false;
        }
        return true;
    }

    /**
     * Exit with a error message and HTTP status code header if the user's group does not have permission to the specified resource
     *
     * @param string    $type 'page' or 'category'
     * @param int       $id the resource's ID
     * @param constant  $access_level see the ACL class for supported constants
     * @param string    $error the error message to echo
     *
     * @return bool true if permission is granted, false if not
     */
    public function exitWithErrorIfDenied($type = '', $id = '', $access_level = ACL_READ, $error = '')
    {
        // Special conditions - approve permission right away
        if (
            $this->isAdmin() === true
            || ($type === 'category' && $this->user_private_category_id === intval($id))
        ) {
            return true;
        }

        // Regular conditions
        $permission_types = $this->acl->getPermissionTypes();
        if ($error == '') {
            $verb = $permission_types[$access_level];
            $subject = 'this item';
            if ($type === 'category') {
                $subject = 'in this ' . $type;
            }
            $error = "Error: your group does not have access to $verb $subject.";
        }
        if ($this->acl->check($type, $id, $access_level) === false) {
            header('HTTP/1.0 403 Forbidden');
            echo $error;
            exit;
        }
        return true;
    }

    /**
     * Is the user in the admin group?
     *
     * @return boolean
     */
    public function isAdmin()
    {
        $admin_group_id = 1; // NOTE: admin's group ID must match this in the database
        if (intval($this->f3->get('SESSION.group_id')) === intval($admin_group_id)) {
            return true;
        }
        return false;
    }

    /**
     * Verify CSRF token
     *
     * @return boolean true if verification passed
     */
    public function verifyCSRF()
    {
        if ($this->f3->get('POST.csrf') === $this->f3->get('SESSION.csrf')) {
            return true;
        }
        return false;
    }

    /**
     * Get list of categories from database that current user has permission for
     * categories that don't belong to other users.
     *
     * @param boolean $includePrivate true to include the logged in user's private "My Logins" category
     * @param boolean $useACL true to restrict access based on ACL group rules. Exception: admin always skips ACL checks
     *
     * @return array of categories
     */
    public function getCategories($useACL = false, $includePrivate = true)
    {
        $categories = array();
        $where = '';
        $params = array();

        if ($includePrivate === true) {
            $where = 'user_id = ? OR';
            $params[] = intval($this->f3->get('SESSION.user_id'));
        }

        $sql = "SELECT  
                    id, sorting, name, user_id 
                FROM 
                    categories
                WHERE
                    $where user_id = 0
                ORDER BY 
                    sorting ASC";

        $categories = $this->db->exec($sql, $params);

        // Always skip ACL checks if the user is an admin
        if ($this->isAdmin()) {
            $useACL = false;
        }

        if ($useACL === true) {
            // Filter categories based on ACL
            $filtered_categories = [];
            if (count($categories > 0)) {
                foreach ($categories as $category) {
                    $slug = \Web::instance()->slug($category['name']);
                    // If user's group has access to the category
                    // Or category is private and parameter specifies inclusion
                    if (
                        $this->acl->check('category', $category['id'], ACL_READ) === true
                        || ($category['user_id'] > 0 && $includePrivate === true)
                    ) {
                        $filtered_categories[] = $category;
                    }
                }
            }
            $categories = $filtered_categories;
        }

        // Set array key as ID of the row
        $rekeyed_categories = [];
        foreach ($categories as $category) {
            $rekeyed_categories[$category['id']] = $category;
        }
        $categories = $rekeyed_categories;

        // Query database
        return $categories;
    }

    /**
     * Get the user's private "My Logins" category ID
     * @return int
     */
    protected function getUserPrivateCategoryId()
    {
        $category = $this->categories->load(['user_id = ?', $this->f3->get('SESSION.user_id')]);
        return intval($category->id);
    }

    /**
     * Get the user's group ID
     * @return int
     */
    protected function getUserGroupId()
    {
        $user = $this->users->load(['id = ?', $this->f3->get('SESSION.user_id')]);
        return intval($user->groupId);
    }

    /**
     * Get list of pages from database
     *
     * @return array
     */
    public function getPages()
    {
        $sql = "SELECT 
                    id, name 
                FROM 
                    pages
                ORDER BY 
                    id ASC";

        // Query database
        return $this->db->exec($sql);
    }

    /**
     * Get list of pages from database
     *
     * @param boolean $exclude_admin exclude the admin group?
     * @return array
     */
    protected function getGroups($exclude_admin = true)
    {
        $sql_where = '';
        
        if ($exclude_admin === true) {
            $sql_where = 'WHERE id != ' . $this->admin_group_id;
        }

        $sql = "SELECT * 
                FROM groups
                $sql_where
                ORDER BY id ASC";

        $groups = $this->db->exec($sql);

        if (false === count($groups) > 0) {
            return false;
        }

        return $groups;
    }

    /**
     * Get all permissions in the ACL database table
     *
     * @param array $resources to store groups list of groups
     * @param array $pages list of pages
     * @param array $categories list of categories
     *
     * @return array list of permissions for all groups
     */
    protected function getPermissions($groups = [], $pages = [], $categories = [])
    {
        $perms = ['groups' => []];

        $resources = [
            'groups'    => $groups,
            'pages'     => $pages,
            'categories'=> $categories
        ];

        $default_perms = [
            'read'   => false,
            'create' => false,
            'edit'   => false,
            'delete' => false
        ];

        $permissionTypes = $this->acl->getPermissionTypes();

        /*
        Set up groups, pages, and categories array structure w/ default permissions
        */

        foreach ($resources as $r_key => $r_value) {
            // If no resource was supplied, get it
            if (empty($r_value)) {
                // Call the relevant method from this class to get the data
                $resources[$r_key] = call_user_func([$this, 'get' . ucfirst($r_key)]);
                // If getting data failed, exit method.
                if ($resources[$r_key] === false) {
                    return false;
                }
            }
        }

        // Add groups list to the permissions array
        foreach ($resources['groups'] as $group) {
            // Add blank arrays for each permission category
            $perms['groups'][$group['id']] = ['pages' => [], 'categories' => []];
            // Add pages default permissions
            foreach ($resources['pages'] as $page) {
                $perms['groups'][$group['id']]['pages'][$page['id']] = $default_perms;
            }
            // Add categories default permissions
            foreach ($resources['categories'] as $category) {
                $perms['groups'][$group['id']]['categories'][$category['id']] = $default_perms;
            }
        }

        // Get ACL entries except for admin group
        $sql = "SELECT * FROM acl WHERE groupId != " . $this->admin_group_id;
        $perms_db = $this->db->exec($sql);
        
        // Sample database row:
        /*
            [0]=>
              array(5) {
                ["id"]=> "1"
                ["groupId"]=> "1"
                ["type"]=> "page"
                ["foreignId"]=> "1"
                ["accessLevel"]=> "8"
            }
        */

        // Restructure permissions by group and type
        /*
            Output structure:
            permissions [
                groups => [
                    (group id) => [
                        pages => [
                            (page id) => [
                                'read'   => true,
                                'create' => true,
                                'edit'   => true,
                                'delete' => false
                            ],
                            ...
                        ],
                        categories => [
                            (same structure as pages)
                        ]
                    ],
                    ...
                ]
            ]
        */
        foreach ($perms_db as $perm) {
            // Determine resource type
            $resource_type = '';
            switch ($perm['type']) {
                case 'page':
                    $resource_type = 'pages';
                    break;
                case 'category':
                    $resource_type = 'categories';
                    break;
                default:
                    // Invalid resource type, exit method
                    die('Invalid resource type');
            }
            
            // Determine permission type
            $perm_type = $permissionTypes[intval($perm['accessLevel'])];
            
            // Set permission on current resource if present
            if ($perm_type !== '') {
                $perms['groups'][$perm['groupId']][$resource_type][$perm['foreignId']][$perm_type] = true;
            }
        }
        return $perms;
    }

    /**
     * Get and store user's access permissions in the hive
     *
     * @param object $acl ACL class instance
     */
    private function storeCurrentUserPermissions($acl)
    {
        /*
         * Store user's permissions
         *
         * Permission types include pages and categories.
         *
         * The ACL database table has one row per permission. For example, if a group
         * had read and create access, there would be two database rows.
         *
         * See the ACL class for constants defining the number related to each permission.
        */

        $default_perms = [
            'read'   => false,
            'create' => false,
            'edit'   => false,
            'delete' => false
        ];

        // Admin has full access by default
        if ($this->isAdmin()) {
            $default_perms = [
                'read'   => true,
                'create' => true,
                'edit'   => true,
                'delete' => true
            ];
        }

        // Get list of pages
        $pages = $this->getPages();
        // Get list of categories - no ACL restrictions, no private categories
        $categories = $this->getCategories(false, false);
        // Set group ID if user's session variable exists
        $user_group_id = 0;
        if ($this->f3->exists('SESSION.group_id')) {
            $user_group_id = intval($this->f3->get('SESSION.group_id'));
        }

        if ($user_group_id > 0) {
            // Store page permissions
            foreach ($pages as $page) {
                $permissions = $default_perms;
                // Create variable-friendly slug from name
                //$slug = \Web::instance()->slug($page['name']);
                // Get permissions from acl
                if ($acl_permissions = $acl->getPermissionsForResource($user_group_id, 'page', $page['id'])) {
                    // Merge default permissions with acl permissions
                    $permissions = array_merge($default_perms, $acl_permissions);
                }
                // Store permissions in the hive
                $this->f3->set('permissions.page.'.$page['id'], $permissions);
            }

            // Store category permissions
            foreach ($categories as $category) {
                $permissions = $default_perms;
                // Create variable-friendly slug from name
                //$slug = \Web::instance()->slug($category['name']);
                // Get permissions from acl
                if ($acl_permissions = $acl->getPermissionsForResource($user_group_id, 'category', $category['id'])) {
                    // Merge default permissions with acl permissions
                    $permissions = array_merge($default_perms, $acl_permissions);
                }
                // Store permissions in the hive
                $this->f3->set('permissions.category.'.$category['id'], $permissions);
            }
        }
    }

    /**
     * Decrypt a field's value from the fields database table
     *
     * @param string $value string to decrypt
     * @return string the decrypted value
     */
    public function decryptFieldValue($value)
    {
        // Decrypt field value
        // If decryption fails, make it blank

        $key = $this->f3->get('CRYPTKEY');
        $key = \Defuse\Crypto\Crypto::hexToBin($key);

        $value = \Defuse\Crypto\Crypto::hexToBin($value);

        try {
            $value = \Defuse\Crypto\Crypto::decrypt($value, $key);
        } catch (\Ex\InvalidCiphertextException $ex) { // VERY IMPORTANT
            // Either:
            //   1. The ciphertext was modified by the attacker,
            //   2. The key is wrong, or
            //   3. $ciphertext is not a valid ciphertext or was corrupted.
            // Assume the worst.
            die('DANGER! The ciphertext has been tampered with!');
        } catch (\Ex\CryptoTestFailedException $ex) {
            $value = '';
            //die('Cannot safely perform decryption');
        } catch (\Ex\CannotPerformOperationException $ex) {
            $value = '';
            //die('Cannot safely perform decryption');
        }
        return $value;
    }
}
