<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('ion_auth');
    }

    public function index()
    {
        $this->load->view('welcome_message');
    }

    public function login()
    {
        $this->data['title'] = "Login";

        $this->load->library('form_validation');
        $this->form_validation->set_rules('username', 'Username', 'trim|required');
        $this->form_validation->set_rules('password', 'Password', 'required');
        $this->form_validation->set_rules('ajax', 'AJAX', 'trim|is_natural');

        if ($this->form_validation->run() === false) {
            if ($this->input->post('ajax')) {
                $response['username_error'] = form_error('username');
                $response['password_error'] = form_error('password');
                header("content-type:application/json");
                echo json_encode($response);
                exit;
            }

            $this->load->helper('form');
            $this->render('user/login_view');
        } else {
            $remember = (bool) $this->input->post('remember');
            $username = $this->input->post('username');
            $password = $this->input->post('password');
            $this->ion_auth->set_hook('post_login_successful', 'get_gravatar_hash', $this, '_gravatar', array());

            $remember = (bool) $this->input->post('remember');

            if ($this->ion_auth->login($username, $password, $remember)) {
                if ($this->input->post('ajax')) {
                    $response['logged_in'] = 1;
                    header("content-type:application/json");
                    echo json_encode($response);
                    exit;
                }

                redirect('dashboard');
            } else {
                if ($this->input->post('ajax')) {
                    $response['username'] = $username;
                    $response['password'] = $password;
                    $response['error'] = $this->ion_auth->errors();
                    header("content-type:application/json");
                    echo json_encode($response);
                    exit;
                }
                $_SESSION['auth_message'] = $this->ion_auth->errors();
                $this->session->mark_as_flash('auth_message');
                redirect('user/login');
            }
        }
    }

    public function ajax_login()
    {
        $response['message'] = 'hello';
        header("content-type:application/json");

        echo json_encode($response);
    }

    public function logout()
    {
        $this->ion_auth->logout();
        redirect('user/login');
    }

    public function _gravatar()
    {
        if ($this->form_validation->valid_email($_SESSION['email'])) {
            $gravatar_url = md5(strtolower(trim($_SESSION['email'])));
            $_SESSION['gravatar'] = $gravatar_url;
        }
        return true;
    }
}
