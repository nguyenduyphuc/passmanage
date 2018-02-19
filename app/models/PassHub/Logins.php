<?php

namespace PassHub;

/**
 * Logins class
 *
 * Allows authenticated users to view and manage logins
 *
 * @package PassHub
 * @version 1.1.0
 * @author Derek Loewen <derek@derekloewen.com>
 * @copyright 2016 Derek Loewen
 */

class Logins extends Model
{
    /**
     * ID of this model's corresponding entry in the 'pages' database table
     * @var integer
     */
    protected $page_id = 1;

    /**
     * Call parent constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show logins page.
     */
    public function view()
    {
        // No need to restrict access to viewing this page. That's handled by categories ACL

        $this->f3->set('mode', 'logins');
        $this->f3->set('content', 'logins.html.php');

        echo \Template::instance()->render('base.html.php');
    }

    /**
     * Retrieve and return all logins in category
     * or return a single login if the ID parameter exists.
     *
     * Use with AJAX request
     * Outputs JSON
     */
    public function get()
    {
        $success = false;
        
        $output = '';
        $data = array();

        $category_id = 0;
        $login_id = 0;
        $keyword = '';

        $params = array();
        $where = '';

        // Verify CSRF token
        if (!$this->verifyCSRF()) {
            header('HTTP/1.0 402 Request Failed');
            exit;
        }

        // Get logins data
        // ------------------------------

        // set category id if the parameter exists
        if (
            $this->f3->exists('POST.categoryId')
            && intval($this->f3->get('POST.categoryId')) > 0
        ) {
            $category_id = intval($this->f3->get('POST.categoryId'));
        }

        // set keyword if the parameter exists
        if (
            $this->f3->exists('POST.keyword')
            && trim($this->f3->get('POST.keyword')) != ''
        ) {
            $keyword = $this->f3->get('POST.keyword');
        }

        // set login ID if the parameter exists
        if (
            $this->f3->exists('PARAMS.id')
            && intval($this->f3->get('PARAMS.id')) > 0
        ) {
            $login_id = intval($this->f3->get('PARAMS.id'));
        }

        // Build query
        $where = '';
        // Category ID
        if ($category_id > 0) {
            $where = 'logins.category_id=?';
            $params[] = $category_id;
        }
        // Keyword
        if ($keyword != '') {
            $glue = 'AND';
            // If WHERE condition is not set, remove AND
            if ($where == '') {
                $glue = '';
            }
            $where .= " $glue logins.name LIKE ?";
            $params[] = '%'.$keyword.'%';
        }
        // Login ID
        if ($login_id > 0) {
            $glue = 'AND';
            // If WHERE condition is not set, remove AND
            if ($where == '') {
                $glue = '';
            }
            $where .= " $glue logins.id=?";
            $params[] = $login_id;
        }

        if ($where != '') {
            $where .= ' AND';
        }

        // Build list of categories IDs user has access to
        $categories = $this->getCategories(true);
        $categoryIds = [];
        foreach ($categories as $category) {
            $categoryIds[] = $category['id'];
        }
        // Create category IDs string for use with SQL query
        $categoryIdsString = implode($categoryIds, ',');

        // If the user-selected category does not match any available categories,
        // they don't have access to it.
        if ($category_id > 0 && (array_search($category_id, $categoryIds) === false)) {
            header('HTTP/1.0 401 Unauthorized');
            exit;
        }

        $sql = "SELECT  
                    id as loginId,
                    name as loginName,
                    category_id as loginCategoryId
                FROM   
                    logins
                WHERE 
                    $where
                    category_id 
                        IN ($categoryIdsString)
                LIMIT 100";

        // Query database
        $logins = $this->db->exec($sql, $params);

        // Test if results were returned, get fields if yes
        if (count($logins) > 0) {

            // Get fields data
            // ------------------------------

            $sql = 'SELECT  
                        id as fieldId,
                        name as fieldName,
                        value as fieldValue,
                        type as fieldType,
                        sorting as fieldSorting,
                        login_id as loginId
                    FROM   
                        fields';

            // Query database
            $fields = $this->db->exec($sql);

            // Create array with this structure:
            // 1 => array(
            //   'loginId' => 1,
            //   'loginName' => 'Faceplace',
            //    'fields' => array(
            //      1 => array(
            //        'fieldId' => 1,
            //        'fieldName' => 'Username',
            //        'fieldValue' => 'test@domain.co',
            //        'fieldType' => 'text'
            //      ), ...
            //    )
            // ) ...
            foreach ($logins as $login) {
                // Create references to simplify variable names
                // and increase sanity
                $loginId = &$login['loginId'];
                $loginName = &$login['loginName'];
                $loginCategoryId = &$login['loginCategoryId'];
                // Insert login data
                $data[$loginId] = array();
                $data[$loginId]['loginId'] = $loginId;
                $data[$loginId]['loginName'] = $loginName;
                $data[$loginId]['loginCategoryId'] = $loginCategoryId;
                // Nest associated fields inside
                foreach ($fields as $field) {
                    // If this field references the parent login
                    if ($field['loginId'] === $login['loginId']) {
                        // Create references to simplify variable names
                        $fieldId = &$field['fieldId'];
                        $fieldName = &$field['fieldName'];
                        $fieldValue = &$field['fieldValue'];
                        $fieldType = &$field['fieldType'];
                        $fieldSorting = &$field['fieldSorting'];

                        // Decrypt field value
                        // If decryption fails, it will be blank
                        $fieldValue = $this->decryptFieldValue($fieldValue);

                        // Add fields parent-level array
                        if (!isset($data[$loginId]['fields'])) {
                            $data[$loginId]['fields'] = array();
                        }
                        // field sub-array
                        if (!isset($data[$loginId]['fields'][$fieldSorting])) {
                            $data[$loginId]['fields'][$fieldSorting] = array(
                                'fieldId' => $fieldId,
                                'fieldName' => $fieldName,
                                'fieldValue' => $fieldValue,
                                'fieldType' => $fieldType,
                            );
                        }
                    }
                }
                // If no fields were inserted into login, insert a blank field
                if (!isset($data[$loginId]['fields'])) {
                    $data[$loginId]['fields'] = array();
                    $data[$loginId]['fields'][] = array(
                        'fieldId' => '',
                        'fieldName' => 'New Field',
                        'fieldValue' => '',
                        'fieldType' => 'text',
                    );
                }
            }

            // Reverse array's order, making it sort from newest to oldest
            if (count($data) > 1) {
                $data = array_reverse($data);
            }

            // If only 1 result was returned, de-nest it 1 level
            if ($login_id > 0) {
                $data = $data[$login_id];
            }
        }

        // Output all data in JSON format
        // ------------------------------

        $this->f3->set('data', array());
        $this->f3->set('data.logins', $data);
        $this->f3->set('data.categories', $categories);

        header('HTTP/1.0 200 OK');
        echo utf8_encode(json_encode($this->f3->get('data')));
    }

    /**
     * Save login & field data to database
     * Use with AJAX request
     */
    public function post()
    {
        $success = false;

        // Verify CSRF token
        if (!$this->verifyCSRF()) {
            header('HTTP/1.0 402 Request Failed');
            exit;
        }

        // Get POST data
        $post = $this->f3->get('POST');
        $loginData = $post['loginData'];
        $fieldData = $post['fieldData'];

        if (!empty($loginData)) {
            // Insert/Update login
            $login_id = intval($loginData['loginId']);
            $category_id = intval($loginData['loginCategoryId']);
            // If login already exists, load it
            $this->logins->load(array('id = ?', intval($loginData['loginId'])));
            // Check permission to edit/add it
            if ($login_id > 0) {
                $this->exitWithErrorIfDenied('category', $category_id, ACL_EDIT);
            } else {
                $this->exitWithErrorIfDenied('category', $category_id, ACL_CREATE);
            }
            // Set new values
            $this->logins->id = $login_id;
            $this->logins->name = $this->f3->clean($loginData['loginName']);
            $this->logins->category_id = $category_id;
            // save
            $success = $this->logins->save();

            // get last inserted row ID for new login
            $last_insert_id = $this->logins->get('_id');
            // use existing row ID for old login
            if (is_null($last_insert_id) === false) {
                $login_id = intval($last_insert_id);
            }

            if (!empty($fieldData)) {
                // Insert/Update fields
                // Loop through each
                foreach ($fieldData as $field) {
                    // If field already exists, load it
                    if (intval($field['fieldIndex']) > 0) {
                        $this->fields->load(array('id = ?', $field['fieldIndex']));
                    }
                    $this->fields->id = intval($field['fieldIndex']);
                    $this->fields->name = $this->f3->clean($field['fieldName']);
                    $this->fields->type = $this->f3->clean($field['fieldType']);
                    $this->fields->sorting = intval($field['fieldSorting']);
                    $this->fields->login_id = $login_id;

                    // Clean & Encrypt field value
                    $field['fieldValue'] = $this->f3->clean($field['fieldValue']);

                    $key = $this->f3->get('CRYPTKEY');
                    $key = \Defuse\Crypto\Crypto::hexToBin($key);

                    try {
                        $encrypted = \Defuse\Crypto\Crypto::encrypt($field['fieldValue'], $key);
                    } catch (\Ex\CryptoTestFailedException $ex) {
                        die('Cannot safely perform encryption');
                    } catch (\Ex\CannotPerformOperationException $ex) {
                        die('Cannot safely perform encryption');
                    }

                    $this->fields->value = \Defuse\Crypto\Crypto::binToHex($encrypted);

                    // Save & reset
                    if (!$this->fields->save()) {
                        $success = false;
                    }
                    $this->fields->reset();
                }
            }
        }

        if ($success) {
            header('HTTP/1.0 200 OK');
            echo $login_id;
        } else {
            header('HTTP/1.0 402 Request Failed');
        }
    }

    /**
     * Delete fields from database by ID
     * Use with AJAX request
     */
    public function deleteFields()
    {
        $success = false;

        // Does the user have permission to delete for the category this login is in?
        if ($this->f3->exists('POST.fieldCategory')) {
            $category_id = intval($this->f3->get('POST.fieldCategory'));
            // Check edit permission since deleting fields is part of editing a login
            $this->exitWithErrorIfDenied('category', $category_id, ACL_EDIT);
        } else {
            header('HTTP/1.0 402 Request Failed');
            echo 'The field category ID was missing.';
            exit;
        }

        // Verify CSRF token
        if (!$this->verifyCSRF()) {
            header('HTTP/1.0 402 Request Failed');
            exit;
        }

        // Fields
        if ($this->f3->exists('POST.deleteFieldsQueue')) {
            $field_ids = $this->f3->get('POST.deleteFieldsQueue');
            foreach ($field_ids as $id) {
                // Validate ID
                if (intval($id) > 0) {
                    $this->fields->load(array('id = ?', intval($id)));
                    if ($this->fields->erase()) {
                        $success = true;
                    }
                    $this->fields->reset();
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
     * Delete login from database by ID
     * Database is set up with foreign key that cascades the delete to fields :)
     * Use with AJAX request
     */
    public function deleteLogin()
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
            // Retrieve ID from URL parameter
            $id = intval($this->f3->get('PARAMS.id'));
            // Load login by ID and erase it if the user has permission
            $this->logins->load(array('id = ?', $id));
            // Check permission to delete
            $this->exitWithErrorIfDenied('category', $this->logins->category_id, ACL_DELETE);
            // Delete login
            if ($this->logins->erase()) {
                $success = true;
            }
            $this->fields->reset();
        }

        if ($success) {
            header('HTTP/1.0 200 OK');
        } else {
            header('HTTP/1.0 402 Request Failed');
        }
    }
}
