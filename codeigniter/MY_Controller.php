<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

	public function __construct() {
		parent::__construct();
	}


    protected function render($the_view = NULL,$data=array() ,$layout = 'layouts/main')
    { 
      $data['content'] = (is_null($the_view)) ? '' : $this->load->view($the_view,$data, TRUE);
      $this->load->view($layout, $data);  
    }

	protected function do_upload($params = array()) {
                $config['upload_path']          = $params['path'];
                $config['allowed_types']        = 'gif|jpg|png|jpeg';
                $config['max_size']             = 100;
                $config['max_width']            = 1024;
                $config['max_height']           = 768;

                if(isset($params['new_name']) && $params['new_name'] ==true) { 
			        $new_name =  time().'_'.uniqid(mt_rand(1000,9999)).'_'.$_FILES[$params['file_name']]['name'];
			        $config['file_name'] =  $new_name;
			    }else{
			        $config['encrypt_name'] =true;   
			    }

                $this->load->library('upload', $config);

                if ( ! $this->upload->do_upload($params['file_name']))
                {
                        $error = array('error' => $this->upload->display_errors());

                        return $error;
                }
                else
                {
                        $data = array('upload_data' => $this->upload->data());

                        return $data;
                }
    }
}


require_once 'REST_Controller.php';
?>
