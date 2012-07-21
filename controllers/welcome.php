<?php
if ( ! defined("BASEPATH"))
{
    exit("No direct script access allowed");
}

class Welcome extends CI_Controller {
    public function index()
    {
        $this->load->library("static_loader");
        $config = $this->static_loader->load(array(
            "welcome",
            "common/_masthead",
        ));
        echo "<textarea>$config</textarea>";
    }
}
/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
