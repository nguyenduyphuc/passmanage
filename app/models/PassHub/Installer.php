<?php

namespace PassHub;

/**
 * Installer class
 *
 * Sets up the PassHub application
 *
 * @package PassHub
 * @version 1.1.0
 * @author Derek Loewen <derek@derekloewen.com>
 * @copyright 2016 Derek Loewen
 */

class Installer
{
    /**
     * Fat-free Framework Instance
     * @var object
     */
    protected $f3;

    /**
     * Database connection
     * @var object
     */
    protected $db;
    
    /**
     * The current installer step
     * @var string
     */
    protected $step;
    
    /**
     * The current error message, if any
     * @var string
     */
    protected $error = '';

    /**
     * Stores F3 instance, sets up Session, sets framework variables.
     * Also cancels installer if the application has already been installed.
     */
    public function __construct()
    {
        // Main F3 instance
        $this->f3 = \Base::instance();

        // Start new session
        new \Session();

        // Set globally available variables
        $this->f3->set('BASEURL', $this->f3->get('SCHEME').'://'.$this->f3->get('HOST').':'.$this->f3->get('PORT').$this->f3->get('BASE'));

        // Set up blank error/success message variables
        $this->f3->set('success', '');
        $this->f3->set('error', '');

        // If installer is not activated, stop and show error
        if ($this->f3->get('ENABLEINSTALLER') === false) {
            $this->step = 'disabled-error';
            $this->show_template();
            exit;
        }
    }

    /**
     * Welcome step.
     */
    public function view()
    {
        $this->step = 'welcome';
        $this->show_template();
    }

    /**
     * System requirements step.
     */
    public function requirements()
    {
        $this->step = 'requirements';

        // Tests array format:
        // key: test name (string)
        // value: test result (boolean)
        $tests = array(
            'Web Server is Apache' => false,
            'PHP Version 5.4+' => false,
            'PDO MySQL Extension' => false,
            'config.ini is Writable' => false,
        );

        // Check if using SSL (only suggest, don't force)
        $ssl_test = false;
        if (
            isset($_SERVER['SERVER_PORT']) &&
            $_SERVER['SERVER_PORT'] === '443'
        ) {
            $ssl_test = true;
        }

        // Are we on Apache?
        if (
            function_exists('apache_get_version')
            || strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false
        ) {
            $tests['Web Server is Apache'] = true;
        }

        // PHP version greater than 5.4
        if (version_compare(phpversion(), '5.4.0', '>=')) {
            $tests['PHP Version 5.4+'] = true;
        }

        // PDO MySQLi extension installed
        if (extension_loaded('pdo_mysql')) {
            $tests['PDO MySQL Extension'] = true;
        }

        // config.ini is writable
        if (is_writable($_SERVER['DOCUMENT_ROOT'].'/'.$this->f3->get('BASE').'/app/config/config.ini')) {
            $tests['config.ini is Writable'] = true;
        }

        $requirements_met = true;

        foreach ($tests as $test) {
            if ($test === false) {
                $requirements_met = false;
                break;
            }
        }

        $this->f3->set('TESTS', $tests);
        $this->f3->set('SSL_TEST', $ssl_test);
        $this->f3->set('REQUIREMENTS_MET', $requirements_met);

        $this->show_template();
    }

    /**
     * Database settings step.
     */
    public function database()
    {
        $this->step = 'database';

        // Form submitted
        // Store and test DB credentials
        // If they connect successfully, continue to the next step
        // If they fail to connect, show error message
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Error handler for catching database connection error
            $this->f3->set('ONERROR',
                function ($f3) {
                    if ($this->f3->get('ERROR.code') === 500) {
                        $this->f3->set('error', 'Error: database connection failed. Please check your details and try again.');
                        $this->show_template();
                    }
                }
            );

            $fields = array(
                'db_host',
                'db_port',
                'db_name',
                'db_username',
                'db_password',
            );

            // Validate and store fields
            foreach ($fields as $field) {
                if (
                    $this->f3->get("POST.{$field}")
                    && trim($this->f3->get("POST.{$field}")) != ''
                ) {
                    $$field = $this->f3->get("POST.{$field}");
                } else {
                    $this->error = true;
                    $this->f3->set('error', 'Error: a field is empty. Please fill out all fields before trying again.');
                }
            }

            // No error, continue testing DB connection
            // with user-submitted form data
            if ($this->error !== true) {
                $this->db = new \DB\SQL(
                    "mysql:host={$db_host};port={$db_port};dbname={$db_name}",
                    $db_username,
                    $db_password
                );

                if (!$this->db) {
                    // Connection failed
                    $this->error = true;
                } else {

                    // Success
                    // 1. store in session
                    // 2. verify database hasn't been installed
                    // 3. insert database schema
                    // 4. then continue to next step

                    // Store in session
                    foreach ($fields as $field) {
                        $this->f3->set("SESSION.{$field}", $$field);
                    }

                    // Verify database hasn't been installed
                    $sql = "SHOW TABLES LIKE 'users'";
                    $result = $this->db->exec($sql);
                    if ($result && !empty($result)) {
                        $this->f3->set('error', 'Error: database is not empty. If this is a new installation, please verify your database is empty before trying again. If you wish to upgrade an existing installation, use the PassHub Updater included with your download.');
                    }

                    // Insert database schema
                    // If the MySQL server supports it, use multi-byte character encoding (utf8mb4).
                    // If not, use utf8
                    if ($this->f3->get('error') == '') {
                        $sql_filename = 'setup-utf8.sql';
                        $mysql_version = $this->db->version();

                        if (version_compare($mysql_version, '5.5.3', '>=')) {
                            $sql_filename = 'setup-utf8mb4.sql';
                        }

                        $sql = $this->f3->read($_SERVER['DOCUMENT_ROOT'].'/'.$this->f3->get('BASE').'/assets/sql/'.$sql_filename);
                        $result = $this->db->exec($sql);
                        if ($result === false) {
                            $this->f3->set('error', 'Error: database could not be populated.');
                        }
                    }

                    // Continue to next step
                    if ($this->f3->get('error') == '') {
                        $this->f3->reroute('@installeremail');
                    }
                }
            }
        }

        // If an error was triggered, the db connection failed
        //if ($this->error === true) {
        //    $this->f3->set('error', 'Error: database connection failed. Please check your details and try again.');
        //}

        $this->show_template();
    }

    /**
     * Email settings step.
     */
    public function email()
    {
        $this->step = 'email';

        // Form submitted
        // Store and test email settings
        // If they send successfully, continue to the next step
        // If they fail to send, show error message
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $smtp_scheme = '';

            $fields = array(
                'email_username',
                'email_password',
                'smtp_server',
                'smtp_port',
                'smtp_scheme',
            );

            // Validate and store fields
            foreach ($fields as $field) {
                if (
                    $this->f3->get("POST.{$field}")
                    && trim($this->f3->get("POST.{$field}")) != ''
                ) {
                    $$field = $this->f3->get("POST.{$field}");
                // SMTP scheme is not required to be filled out
                // but all other fields are
                } elseif ($field !== 'smtp_scheme') {
                    $this->error = 'Error: please fill out all fields';
                }
            }

            // No error, continue sending test email
            if ($this->error === '') {

                // Send test email
                $mail = new \SMTP($smtp_server, $smtp_port, $smtp_scheme, $email_username, $email_password);
                $mail->set('from', '"PassHub" <'.$email_username.'>');
                $mail->set('to', '"Test" <caffeinegfx@gmail.com>');
                $mail->set('subject', 'Passhub Installer SMTP Test');

                if (!$mail->send('Test message')) {
                    // Send failed
                    $this->error = 'Error: test email could not be sent. Please check your details and try again.';
                } else {
                    // Success

                    // Store in session
                    foreach ($fields as $field) {
                        $this->f3->set("SESSION.{$field}", $$field);
                    }

                    // Continue to next step
                    $this->f3->reroute('@installeradmin');
                }
            }
        }

        // If an error was triggered, the test email failed to send
        if ($this->error != '') {
            $this->f3->set('error', $this->error);
        }

        $this->show_template();
    }

    /**
     * Admin user setup step.
     */
    public function admin()
    {
        $this->step = 'admin';

        // Form submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fields = array(
                'email',
                'password',
            );

            // Validate and store fields
            foreach ($fields as $field) {
                if (
                    $this->f3->get("POST.{$field}")
                    && trim($this->f3->get("POST.{$field}")) != ''
                ) {
                    $$field = $this->f3->get("POST.{$field}");
                } else {
                    $this->error = true;
                }
            }
            // No error, continue creating admin user
            if ($this->error !== true) {

                // Get DB connection info
                $db_host = $this->f3->get('SESSION.db_host');
                $db_port = $this->f3->get('SESSION.db_port');
                $db_name = $this->f3->get('SESSION.db_name');
                $db_username = $this->f3->get('SESSION.db_username');
                $db_password = $this->f3->get('SESSION.db_password');

                // New MySQL connection
                $this->db = new \DB\SQL(
                    "mysql:host={$db_host};port={$db_port};dbname={$db_name}",
                    $db_username,
                    $db_password
                );

                // Init database mappers
                $users = new \DB\SQL\Mapper($this->db, 'users');
                $categories = new \DB\SQL\Mapper($this->db, 'categories');

                // Insert Admin user
                $users->name = 'Admin';
                $users->email = $email;
                $users->groupId = 1;
                $users->password = \Bcrypt::instance()->hash($password, md5(time() * rand(5, 500)), 14);

                if ($users->save()) {

                    // get last inserted row ID for new item
                    $id = $users->get('_id');

                    // Create category for the user
                    $categories->name = 'My Logins';
                    $categories->user_id = $id;
                    $categories->save();

                    // Continue to next step
                    $this->f3->reroute('@installercomplete');
                } else {
                    $this->error = true;
                }
            }
        }

        // If an error was triggered, creating the user failed
        if ($this->error === true) {
            $this->f3->set('error', 'Error: Creating Admin user failed. If this problem happens again, try restarting the install process.');
        }

        $this->show_template();
    }

    /**
     * Installer completed step.
     */
    public function complete()
    {
        $this->step = 'complete';

        // Generate a new encryption key for the config file

        try {
            $key = \Defuse\Crypto\Crypto::createNewRandomKey();
                // WARNING: Do NOT encode $key with bin2hex() or base64_encode(),
                // they may leak the key to the attacker through side channels.
        } catch (\Ex\CryptoTestFailedException $ex) {
            die('Cannot safely create a key');
        } catch (\Ex\CannotPerformOperationException $ex) {
            die('Cannot safely create a key');
        }

        $key = \Defuse\Crypto\Crypto::binToHex($key);
        $this->f3->set('cryptkey', $key);

        // Write the config.ini file with user-supplied input
        $config_contents = \Template::instance()->render('config.ini.txt', 'text/html');
        $this->f3->write($_SERVER['DOCUMENT_ROOT'].'/'.$this->f3->get('BASE').'/app/config/config.ini', $config_contents);
        $this->show_template();
    }

    /**
     * Show the template relevant to the current step.
     */
    private function show_template()
    {
        $this->f3->set('step', $this->step);
        $this->f3->set('content', 'installer-'.$this->step.'.html.php');
        echo \Template::instance()->render('installer.html.php');
    }
}
