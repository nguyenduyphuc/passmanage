<?php

namespace PassHub;

/**
 * Categories class
 *
 * Allows permitted users to manage categories
 *
 * @package PassHub
 * @version 1.1.0
 * @author Derek Loewen <derek@derekloewen.com>
 * @copyright 2016 Derek Loewen
 */

class Categories extends Model
{
    /**
     * ID of this model's corresponding entry in the 'pages' database table
     * @var integer
     */
    protected $page_id = 2;

    /**
     * Call parent constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show categories page.
     */
    public function view()
    {
        // Does the user have access to this resource?
        $this->showErrorIfDenied('page', $this->page_id);

        $this->f3->set('mode', 'categories');
        $this->f3->set('content', 'categories.html.php');
        echo \Template::instance()->render('base.html.php');
    }

    /**
     * Output list of categories in JSON format
     * Use with AJAX request
     *
     * @param boolean $includePrivate whether to include the user's private category or not
     */
    public function get($includePrivate = false)
    {
        // Verify CSRF token
        if (!$this->verifyCSRF()) {
            header('HTTP/1.0 402 Request Failed');
            exit;
        }

        // Use ACL when getting categories?
        $useACL = true;
        // If the user has access to the "Categories" page, disable ACL so they can see all categories
        if ($this->acl->check('page', $this->page_id, ACL_READ) === true) {
            $useACL = false;
        }

        if ($categories = $this->getCategories($useACL, $includePrivate)) {
            header('HTTP/1.0 200 OK');
            echo utf8_encode(json_encode($categories));
        } else {
            header('HTTP/1.0 402 Request Failed');
        }
    }

    /**
     * Get list of categores, including user's private category
     */
    public function getWithPrivate()
    {
        $this->get(true);
    }

    /**
     * Save category data to database
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
        $field_data = $post['categoryData'];

        // Insert/Update field data
        if (!empty($field_data)) {
            foreach ($field_data as $field) {

                // If the category already exists, load it
                if (intval($field['fieldIndex']) > 0) {
                    $this->categories->load(array('id = ?', intval($field['fieldIndex'])));
                    // If a fieldIndex is set, but does not match a row in the database,
                    // the page is out of sync with the database - exit with an error message.
                    if ($this->categories->id == 0 || is_null($this->categories->id)) {
                        header('HTTP/1.0 402 Request Failed');
                        echo 'Error: page is out of sync with database. Please refresh and try again.';
                        exit;
                    }
                }

                // Check permissions to edit/add categories
                if (
                    intval($field['fieldIndex']) > 0
                    &&
                    (
                        $this->categories->name !== $this->f3->clean($field['fieldValue'])
                        || intval($this->categories->sorting) !== (intval($field['fieldSorting']) + 1)
                    )
                ) {
                    // Existing category, check edit permission
                    $this->exitWithErrorIfDenied('page', $this->page_id, ACL_EDIT);
                } else {
                    // If the category is new, check create permission
                    $this->exitWithErrorIfDenied('page', $this->page_id, ACL_CREATE);
                }
                
                // Set values
                $this->categories->id = intval($field['fieldIndex']);
                $this->categories->name = $this->f3->clean($field['fieldValue']);
                $this->categories->sorting = intval($field['fieldSorting']) + 1; /* Add 1 to sorting so user-specific category will remain on top */
                // Save & reset
                if (!$this->categories->save()) {
                    $success = false;
                } else {
                    $success = true;
                }
                $this->categories->reset();
            }
        }

        if ($success) {
            header('HTTP/1.0 200 OK');
        } else {
            header('HTTP/1.0 402 Request Failed');
        }
    }

    /**
     * Delete categories from database by ID
     * Use with AJAX request
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

        $categoryIdsString = '';

        // Delete each category
        if ($this->f3->exists('POST.deleteCategoriesQueue')) {
            $categoryIds = $this->f3->get('POST.deleteCategoriesQueue');
            foreach ($categoryIds as $id) {
                // Validate ID
                if (intval($id) > 0) {
                    // build comma separated string for later
                    $categoryIdsString .= intval($id).', ';
                    // Load and delete each category
                    $this->categories->load(array('id = ?', intval($id)));
                    if ($this->categories->erase()) {
                        $success = true;
                    }
                    $this->categories->reset();
                }
            }
        }

        // Move logins associated with deleted categories to first category
        $categoryIdsString = substr($categoryIdsString, 0, -2); /* trim comma + space separated string */
        if ($this->logins->load(array("category_id IN ($categoryIdsString)"))) {
            $this->logins->category_id = 1;
            $this->logins->save();
            $this->logins->reset();
        }
        // Move ACL records
        $sql = "DELETE  
                FROM   
                    acl
                WHERE 
                    type = 'category' 
                    AND foreignId IN ($categoryIdsString)
                LIMIT 10000";

        // Query database
        $this->db->exec($sql);

        if ($success) {
            header('HTTP/1.0 200 OK');
        } else {
            header('HTTP/1.0 402 Request Failed');
        }
    }
}
