<?php

define('ACL_READ', 1);
define('ACL_CREATE', 2);
define('ACL_EDIT', 4);
define('ACL_DELETE', 8);

/**
 *  Access Control List for authorization by group.
 *
 *  @author modified by Derek Loewen <derek@derekloewen.com>, originally by Philipp Hirsch <itself@hanspolo.net>
 *  @license https://gnu.org/licenses/gpl.html GNU Public License
 *
 *  @version 0.2
 */
class ACL extends SqlMapper
{
    /**
     * A list of all permission types
     * @var array
     */
    protected $permission_types = [];

    /**
     *  @see SQLMapper::__construct($db, $table, $ttl)
     */
    public function __construct($db, $table = 'acl', $ttl = 60)
    {
        parent::__construct($db, $table, $ttl);

        $this->properties = array(
            'id' => array(
                'type' => 'Integer',
            ),
            'groupId' => array(
                'type' => 'Integer',
            ),
            'type' => array(
                'type' => 'Text',
            ),
            'foreignId' => array(
                'type' => 'Integer',
            ),
            'accessLevel' => array(
                'type' => 'Integer',
            ),
        );

        $this->permission_types = $this->getPermissionTypes();
    }

    /**
     *  Get the group's permissions for a specified resource
     *
     *  @param Integer $group_id
     *  @param String $type the type of resource requested (page or category)
     *
     *  @return Boolean
     *
     *  @throws TypeNotFoundException
     */
    public function getPermissionsForResource($group_id, $type, $foreign_id)
    {
        $permissions = [];

        // Validate/Filter parameters

        $group_id = intval($group_id);
        $foreign_id = intval($foreign_id);
        
        // Match the $type to its table
        
        $foreign_table = '';
        
        switch ($type) {
            case 'page':
                $foreign_table = 'pages';
                break;
            case 'category':
                $foreign_table = 'categories';
                break;
            default:
                throw new TypeNotFoundException("The '$type' type is not in the acl database table.");
                break;
        }

        // Find acl rows for the matching group ID, type, and foreignId
        
        $sql = "SELECT
                    accessLevel
                FROM 
                    acl
                INNER JOIN 
                    $foreign_table 
                ON 
                    acl.foreignId = {$foreign_table}.id
                WHERE 
                    acl.groupId = $group_id
                    AND {$foreign_table}.id = $foreign_id
                    AND acl.type = '{$type}'
                LIMIT 100";

        $result = $this->db->exec($sql);

        if (count($result) < 1) {
            return false;
        }

        /*
         * Reformat results into an array like this:
         *
         * Array
         * (
         *     [read] => true
         *     [edit] => true
         *     ...
         * )
         */

        foreach ($result as $row) {
            if (intval($row['accessLevel']) > 0) {
                $type = $this->permission_types[intval($row['accessLevel'])];
                $permissions[$type] = true;
            }
        }

        return $permissions;
    }

    /**
     * Checks if the group has permission to perform a specified action on a specified resource
     *
     * @param $type string the type of resource, example: 'page'
     * @param $id string the resource's id
     * @param $access_level int see constants defined at the top of this file
     * @return boolean true if access OK, false if access denied
     */
    public function check($type, $id, $access_level)
    {
        $f3 = \Base::instance();

        if (
            $f3->get('permissions.'.$type.'.'.$id)
            && $f3->get('permissions.'.$type.'.'.$id.'.'.$this->permission_types[intval($access_level)]) === true
        ) {
            return true;
        }
        return false;
    }

    /**
     * Get the corresponding access level integer for a permission type
     * @return int access level number or bool false if $perm_name is invalid
     */
    public function getAccessLevelNumber($perm_name = '')
    {
        $access_level_num = 0;

        if ($perm_name === '') {
            return false;
        }
        
        switch (strtolower($perm_name)) {
            case 'read':
                $access_level_num = ACL_READ;
                break;
            case 'create':
                $access_level_num = ACL_CREATE;
                break;
            case 'edit':
                $access_level_num = ACL_EDIT;
                break;
            case 'delete':
                $access_level_num = ACL_DELETE;
                break;
            default:
                return false;
        }

        return $access_level_num;
    }

    // TODO: create getAccessLevelName method to use in Model->getPermissions()

    /**
     * Get an associative list of permissions
     * @return array of permission types - the key and its corresponding name
     */
    public function getPermissionTypes()
    {
        return [
            ACL_READ    => 'read',
            ACL_CREATE  => 'create',
            ACL_EDIT    => 'edit',
            ACL_DELETE  => 'delete',
        ];
    }
}

class TypeNotFoundException extends Exception
{
}
