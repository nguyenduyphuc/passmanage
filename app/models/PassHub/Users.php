<?php

namespace PassHub;

/**
 * Users class
 *
 * Allows permitted users to manage users.
 * Also handles the user editing their own account.
 *
 * @package PassHub
 * @version 1.1.0
 * @author Derek Loewen <derek@derekloewen.com>
 * @copyright 2016 Derek Loewen
 */

class Users extends Model
{
    /**
     * ID of this model's corresponding entry in the 'pages' database table
     * @var integer
     */
    protected $page_id = 3;

    /**
     * Current mode of operation. Can be users or edit-account.
     * @var string
     */
    private $mode = 'users';

    /**
     * Call parent constructor and set current mode
     */
    public function __construct()
    {
        parent::__construct();

        // Set current mode
        $alias = $this->f3->get('ALIAS');
        // If the editaccount route is called, switch to that mode
        if ($alias == 'editaccount') {
            $this->mode = 'edit-account';
        }
        $this->f3->set('mode', $this->mode);
    }

    /**
     * Show users page.
     */
    public function view()
    {
        // Does user have access to this area?
        // Only check if they're on the users page
        if ($this->mode === 'users') {
            $this->showErrorIfDenied('page', $this->page_id);
        }

        $this->f3->set('content', 'users.html.php');
        echo \Template::instance()->render('base.html.php');
    }

    /**
     * Retrieve and return all users
     * or return a single user if the ID parameter exists.
     *
     * Use with AJAX request
     * Outputs JSON
     */
    public function get()
    {
        $success = false;

        // Verify CSRF token
        if (!$this->verifyCSRF()) {
            header('HTTP/1.0 402 Request Failed');
            exit;
        }
        
        // Get name of current URL route
        $alias = $this->f3->get('ALIAS');

        $output = '';
        $data = array();

        $user_id = 0;
        $keyword = '';

        $params = array();
        $where = '';

        // Check read permision for user routes
        if ($alias !== 'editaccountget') {
            $this->exitWithErrorIfDenied('page', $this->page_id, ACL_READ);
        }

        // Set user ID for the usersgetid route
        if (
            $alias == 'usersgetid'
            && $this->f3->exists('PARAMS.id')
            && intval($this->f3->get('PARAMS.id')) > 0
        ) {
            $user_id = intval($this->f3->get('PARAMS.id'));
        }

        // Set user ID for the editaccount route
        if ($alias == 'editaccountget') {
            $user_id = intval($this->f3->get('SESSION.user_id'));
        }

        if ($user_id > 0) {
            $where = 'WHERE id=?';
            $params[] = $user_id;
        }

        // Get users
        // -------------------------------------

        // Prepare query
        $sql = "SELECT 
                    id as userId,
                    name as userName,
                    email as userEmail,
                    groupId as userGroupId
                FROM users
                $where
                ORDER BY id ASC";
        // Query database
        $users = $this->db->exec($sql, $params);

        // Get ACL groups
        // -------------------------------------

        // Prepare query
        $sql = 'SELECT 
                    id as groupId,
                    name as groupName
                FROM groups
                ORDER BY id ASC';
        // Query database
        $groups = $this->db->exec($sql, $params);
        // Make admin user last
        if (count($groups) > 1) {
            $groups = array_reverse($groups);
        }

        // Output results in JSON format
        // -------------------------------------

        if (
            count($users) > 0
            && count($groups) > 0
        ) {
            $success = true;
            $this->f3->set('data', array());
            $this->f3->set('data.users', $users);
            $this->f3->set('data.groups', $groups);
            header('HTTP/1.0 200 OK');
            echo utf8_encode(json_encode($this->f3->get('data')));
        }
        if ($success === false) {
            header('HTTP/1.0 402 Request Failed');
        }
    }

    /**
     * Save user to database.
     *
     * Use with AJAX request
     * Outputs ID of the user
     */
    public function post()
    {
        $success = false;
        
        // Verify CSRF token
        if (!$this->verifyCSRF()) {
            header('HTTP/1.0 402 Request Failed');
            exit;
        }
        
        // Get current URL route
        $alias = $this->f3->get('ALIAS');

        $post = $this->f3->get('POST');
        $itemData = $post['itemData'];
        $id = 0;

        if (!empty($itemData)) {

            // Insert/Update

            // Avoid duplicate emails
            // Make sure it isn't already assigned to a user
            // ...but allow them to save if they re-enter their email
            if ($this->users->load(array('email = ?', $itemData['userEmail']))) {
                if (intval($this->users->id) != ($itemData['userId'])) {
                    header('HTTP/1.0 402 Request Failed');
                    echo 'Error: email is already assigned to a user.';
                    exit;
                }
            }

            if (intval($itemData['userId']) > 0) {
                // User exists
                // Check permission, unless the user is editing their own account
                if (
                    intval($itemData['userId']) !== intval($this->f3->get('SESSION.user_id'))
                    && $this->acl->check('page', $this->page_id, ACL_EDIT) === false
                ) {
                    header('HTTP/1.0 403 Forbidden');
                    echo 'Error: your group is not authorized to edit users.';
                    exit;
                }
                // Load user row
                $this->users->load(array('id = ?', intval($itemData['userId'])));
            } else {
                // New user
                // Check permission
                if ($this->acl->check('page', $this->page_id, ACL_CREATE) === false) {
                    header('HTTP/1.0 403 Forbidden');
                    echo 'Error: your group is not authorized to create users.';
                    exit;
                }
            }
            $this->users->id = intval($itemData['userId']);
            $this->users->name = $this->f3->clean($itemData['userName']);
            $this->users->email = $this->f3->clean($itemData['userEmail']);
            // Only update groupId in users mode (not edit account mode)
            if ($alias == 'userssave') {
                $this->users->groupId = intval($itemData['userGroupId']);
            }
            // Update password if filled in
            if ($itemData['userPassword'] != '') {
                $this->users->password = \Bcrypt::instance()->hash($itemData['userPassword'], md5(time() * rand(5, 500)), 14);
            }
            // Also update SESSION if data is for currently logged in user
            if (intval($itemData['userId']) === intval($this->f3->get('SESSION.user_id'))) {
                $this->f3->set('SESSION.user_name', $this->f3->clean($itemData['userName']));
            }
            // DB save & reset
            if (!$this->users->save()) {
                $success = false;
            } else {
                $success = true;
            }
            // Get last inserted row ID for new item
            $id = $this->users->get('id');
            $this->users->reset();

            // If the user is new, create a category for them
            if (intval($itemData['userId']) === 0) {
                $this->categories->reset();
                $this->categories->name = 'My Logins';
                $this->categories->user_id = $id;
                $this->categories->save();
                $this->categories->reset();
            }
        }

        if ($success) {
            header('HTTP/1.0 200 OK');
            echo $id;
        } else {
            header('HTTP/1.0 402 Request Failed');
        }
    }

    /**
     * Delete user from database by ID
     * and delete their associated category.
     *
     * Use with AJAX request
     */
    public function delete()
    {
        $success = false;

        // Verify CSRF token
        if (!$this->verifyCSRF()) {
            header('HTTP/1.0 402 Request Failed');
            exit;
        }

        // If ID parameter is valid
        if (
            $this->f3->exists('PARAMS.id')
            && intval($this->f3->get('PARAMS.id')) > 0
        ) {
            $id = intval($this->f3->get('PARAMS.id'));

            // If the specified user id does not match the current user's id
            // check if they have permission to edit other users
            if ($id !== intval($this->f3->get('SESSION.user_id'))) {
                if ($this->acl->check('page', $this->page_id, ACL_DELETE) === false) {
                    // No permission
                    header('HTTP/1.0 403 Forbidden');
                    echo 'Your group does not have permission to delete users.';
                    exit;
                }
            }
            // Delete user
            $this->users->load(array('id = ?', $id));
            if ($this->users->erase()) {
                $success = true;
            }
            $this->users->reset();

            // Delete their category
            $this->categories->load(array('user_id = ?', $id));
            $this->categories->erase();
            $this->categories->reset();
        }

        if ($success) {
            header('HTTP/1.0 200 OK');
        } else {
            header('HTTP/1.0 402 Request Failed');
        }
    }
}
