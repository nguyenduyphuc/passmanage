<?php

namespace PassHub;

/**
 * AppAuth class
 *
 * Authenticates the user and allows them to log out or reset their password
 *
 * @package PassHub
 * @version 1.1.0
 * @author Derek Loewen <derek@derekloewen.com>
 * @copyright 2016 Derek Loewen
 */

class AppAuth extends Model
{
    /**
     * Sets whether the current request is a password reset
     * and redirects authenticated users to the home page
     */
    public function __construct()
    {
        parent::__construct();

        // Password reset flag
        $this->f3->set('reset', false);

        // If already logged in, direct to the home page
        // UNLESS they're logging out
        if (
            $this->f3->get('ALIAS') !== 'logout'
            && $this->f3->exists('SESSION.logged_in')
            && intval($this->f3->get('SESSION.logged_in')) == 1
        ) {
            $this->f3->reroute('/');
        }
    }

    /**
     * Show login form.
     */
    public function view()
    {
        $this->f3->set('mode', 'auth');
        echo \Template::instance()->render('auth.html.php');
    }

    /**
     * Verify user-submitted credentials are accurate
     * then redirect them to the home page.
     */
    public function checkLogin()
    {
        $user = \User::createUser($this->db);
        // Get submitted values
        $email = $this->f3->get('POST.email');
        $password = $this->f3->get('POST.password');
        // Verify login credentials
        if ($user->checkLogin($email, $password) !== false) {
            // Get current user information
            $this->f3->set('user', $this->db->exec('SELECT id, groupId, name, email FROM users WHERE email=?', $email));
            // Store user's SESSION details
            $this->f3->set('SESSION.logged_in', 1);
            $this->f3->set('SESSION.group_id', $this->f3->get('user.0.groupId'));
            $this->f3->set('SESSION.user_id', $this->f3->get('user.0.id'));
            $this->f3->set('SESSION.user_name', $this->f3->get('user.0.name'));
            // Go to the home page
            $this->f3->reroute('/');
        } else {
            $this->f3->set('error', 'incorrect login, please try again');
        }
        $this->view();
    }

    /**
     * Destroy user session and redirect to login page.
     */
    public function logout()
    {
        $this->f3->clear('SESSION.logged_in');
        $this->f3->clear('SESSION.group_id');
        $this->f3->clear('SESSION.user_id');
        $this->f3->reroute('/');
    }

    /**
     * Reset the user's password.
     */
    public function reset()
    {
        $this->f3->set('reset', true);
        // If there's a reset key, it's password reset time
        if ($this->f3->exists('PARAMS.resetKey')) {
            $reset_key = $this->f3->get('PARAMS.resetKey');
            // generate new password and encrypt it
            $new_password_plain = \PasswordGenerator::getAlphaNumericPassword(10);
            $new_password_encrypted = \Bcrypt::instance()->hash($new_password_plain, md5(time() * rand(5, 500)), 14);
            // update password in database and remove resetKey
            if (!$this->db->exec(
                    'UPDATE users SET password=:password, resetKey="" WHERE resetKey=:reset',
                    array(
                        ':password' => $new_password_encrypted,
                        ':reset' => $reset_key,
                    )
                )
            ) {
                $this->f3->set('error', 'The reset key is not valid. Please start the reset process again.');
            } else {
                $this->f3->set('reset', false);
                $this->f3->set('success', 'Your new password is: <code>'.$new_password_plain.'</code><br>Please store it in a safe place.');
            }
        }
        $this->view();
    }

    /**
     * Email the user with a password reset link.
     */
    public function sendResetEmail()
    {
        // Get form data
        $email = $this->f3->get('POST.email');

        // Generate & store reset key in database
        $reset_key = md5(time() * rand(5, 500));
        // Make sure the email provided is associated with an account
        if (!$this->db->exec('UPDATE users SET resetKey=? WHERE email=?', array($reset_key, $email))) {
            // No, show error
            $this->f3->set('reset', true);
            $this->f3->set('error', 'that email is not associated with any accounts');
        } else {
            // Yes, generate reset link and send email to user
            $this->f3->set('password_reset_link', $this->f3->get('BASEURL').$this->f3->alias('authreset').'/'.$reset_key);
            // Send email
            $mail = new \SMTP($this->f3->get('SMTP_SERVER'), $this->f3->get('SMTP_PORT'), $this->f3->get('SMTP_SCHEME'), $this->f3->get('EMAIL'), $this->f3->get('EMAIL_PW'));
            $mail->set('from', '"PassHub" <'.$this->f3->get('EMAIL').'>');
            $mail->set('to', '"<'.$this->f3->get('POST.email').'>');
            $mail->set('subject', 'Password Reset');
            $mail->send(\Template::instance()->render('emailreset.txt'));
            // Show template
            $this->f3->set('success', 'OK, reset instructions have been sent to your email.');
        }
        $this->view();
    }
}
