<?php

namespace PassHub;

/**
 * Tools class
 *
 * Allows permitted users to use system management tools
 *
 * @package PassHub
 * @version 1.1.0
 * @author Derek Loewen <derek@derekloewen.com>
 * @copyright 2016 Derek Loewen
 */

class Tools extends Model
{
    /**
     * ID of this model's corresponding entry in the 'pages' database table
     * @var integer
     */
    protected $page_id = 5;

    /**
     * Call parent constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show page.
     */
    public function view()
    {
        // Does the user have access to this resource?
        $this->showErrorIfDenied('page', $this->page_id);

        $this->f3->set('mode', 'tools');
        $this->f3->set('content', 'tools.html.php');
        echo \Template::instance()->render('base.html.php');
    }

    /**
     * Download a CSV file of all login data
     */
    public function downloadLoginsCsv()
    {
        // Does the user have access to this resource?
        $this->showErrorIfDenied('page', $this->page_id, ACL_CREATE);

        $filename = 'passhub-logins-' . date('Y-m-d') . '.csv';
        $eol = "\r\n";
        $delimiter = ',';
        $output = '';
        $output_row = '';
        $rows = [];

        // Prepare data and insert into rows array

        // Insert header row
        $rows[] = ['ID', 'Name', 'Category ID', 'Category Name', 'Field 1', 'Field 2', 'Field 3', 'Field 4', 'Field 5', 'Field 6', 'Field 7', 'Field 8', 'Field 9', 'Field 10',];

        // Get logins, categories, and fields.
        $sql = "SELECT * FROM logins LIMIT 1000000";
        $logins = $this->db->exec($sql, $params);
        $categories = $this->getCategories(false, false);
        $sql = "SELECT * FROM fields LIMIT 1000000";
        $fields = $this->db->exec($sql, $params);

        // Organize fields by login_id
        $rekeyed_fields = [];
        foreach ($fields as $field) {
            $rekeyed_fields[$field['login_id']][$field['id']] = $field;
        }
        $fields = $rekeyed_fields;

        foreach ($logins as $login) {
            $row = []; // Reset row
            // Insert login table fields
            foreach ($login as $key => $value) {
                $row[$key] = $value;
            }
            // Insert category name
            if (isset($categories[intval($login['category_id'])])) {
                $row['Category Name'] = $categories[intval($login['category_id'])]['name'];
            } else {
                $row['Category Name'] = 'My Logins';
            }
            // Decrypt and insert field values
            foreach ($fields[$login['id']] as $key => $value) {
                $row[] = $this->decryptFieldValue($value['value']);
            }

            $rows[] = $row; // Insert row
        }

        // Generate output
        foreach ($rows as $row => $cols) {
            $output_row = '';
            foreach ($cols as $col) {
                // Escape all double quote characters
                $col = str_replace('"', '""', $col);
                $output_row .= '"' . $col . '"' . $delimiter;
            }
            $output_row = rtrim($output_row, $delimiter) . $eol;
            $output .= $output_row;
        }

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename='.$filename);
        echo $output;
    }
}
