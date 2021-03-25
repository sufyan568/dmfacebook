<?php

require_once("Home.php"); // loading home controller

/**
* @category controller
* class Admin
*/

class Social_apps extends Home
{
    
    public function __construct()
    {
        parent::__construct();

        if ($this->session->userdata('logged_in')!= 1) {
            redirect('home/login', 'location');
        }

        $function_name=$this->uri->segment(2);
        $pinterest_action_array = array('pinterest_settings', 'pinterest_settings_data', 'add_pinterest_settings','edit_pinterest_settings', 'pinterest_settings_update_action', 'delete_app_pinterest', 'change_app_status_pinterest','pinterest_intermediate_account_import_page');

        if(!in_array($function_name, $pinterest_action_array)) 
        {
            if ($this->session->userdata('user_type')== "Member" && $this->config->item("backup_mode")==0)
            redirect('home/login', 'location');        
        }        
        
        $this->load->helper('form');
        $this->load->library('upload');
        
        $this->upload_path = realpath(APPPATH . '../upload');
        set_time_limit(0);

        $this->important_feature();
        $this->periodic_check();

    }


    public function index()
    {
        $this->settings();
    }


    public function settings()
    {

        $data['page_title'] = $this->lang->line('Social Apps');

        $data['body'] = 'admin/social_apps/settings';
        $data['title'] = $this->lang->line('Social Apps');

        $this->_viewcontroller($data);
    }


    public function google_settings()
    {

        if ($this->session->userdata('user_type') != 'Admin')
        redirect('home/login_page', 'location');

        $google_settings = $this->basic->get_data('login_config');

        if (!isset($google_settings[0])) $google_settings = array();
        else $google_settings = $google_settings[0];

        if($this->is_demo == '1')
        {
            $google_settings['api_key'] = 'XXXXXXXXXXX';
            $google_settings['google_client_secret'] = 'XXXXXXXXXXX';
        }

        $data['google_settings'] = $google_settings;
        $data['page_title'] = $this->lang->line('Google App Settings');
        $data['title'] = $this->lang->line('Google App Settings');
        $data['body'] = 'admin/social_apps/google_settings';

        $this->_viewcontroller($data);
    }



    public function google_settings_action()
    {
        if($this->is_demo == '1')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
            exit();
        }

        if ($this->session->userdata('user_type') != 'Admin')
        redirect('home/login_page', 'location');

        if (!isset($_POST)) exit;

        $this->form_validation->set_rules('google_client_id', $this->lang->line("Client ID"), 'trim|required');
        $this->form_validation->set_rules('google_client_secret', $this->lang->line("Client Secret"), 'trim|required');

        if ($this->form_validation->run() == FALSE) 
            $this->google_settings();
        else {

            $insert_data['app_name'] = $this->input->post('app_name');
            $insert_data['api_key'] = $this->input->post('api_key');
            $insert_data['google_client_id'] = $this->input->post('google_client_id');
            $insert_data['google_client_secret'] = $this->input->post('google_client_secret');
            
            $status = $this->input->post('status');
            if($status=='') $status='0';
            $insert_data['status'] = $status;

            $google_settings = $this->basic->get_data('login_config');

            if (count($google_settings) > 0 ) {

                $id = $google_settings[0]['id'];
                $this->basic->update_data('login_config', array('id' => $id), $insert_data);
            }
            else 
                $this->basic->insert_data('login_config', $insert_data);

            $this->session->set_flashdata('success_message', '1');
            redirect(base_url('social_apps/google_settings'),'location');
        }
    }



    protected function facebookTokenValidityCheck($input_token)
    {

        if($input_token=="") 
        return "<span class='badge badge-status text-danger'><i class='fas fa-times-circle red'></i> ".$this->lang->line('Invalid')."</span>";
        $this->load->library("fb_rx_login"); 
        
        if($this->config->item('developer_access') == '1')
        {
            $valid_or_invalid = $this->fb_rx_login->access_token_validity_check_for_user($input_token);
            
            if($valid_or_invalid)
                return "<span class='badge badge-status text-success'><i class='fa fa-check-circle green'></i> ".$this->lang->line('Valid')."</span>";
            else
                return "<span class='badge badge-status text-danger'><i class='fa fa-clock-o red'></i> ".$this->lang->line('Expired')."</span>";
        }
        else
        {
            $url="https://graph.facebook.com/debug_token?input_token={$input_token}&access_token={$input_token}";
            $result= $this->fb_rx_login->run_curl_for_fb($url);
            $result = json_decode($result,true);
             
            if(isset($result["data"]["is_valid"]) && $result["data"]["is_valid"])
                return "<span class='badge badge-status text-success'><i class='fa fa-check-circle green'></i> ".$this->lang->line('Valid')."</span>";
            else
                return "<span class='badge badge-status text-danger'><i class='fa fa-clock-o red'></i> ".$this->lang->line('Expired')."</span>"; 
        }

    }



    public function facebook_settings()
    {
        $data['page_title'] = $this->lang->line('Facebook App Settings');
        $data['title'] = $this->lang->line('Facebook App Settings');
        $data['body'] = 'admin/social_apps/facebook_app_settings';

        $this->_viewcontroller($data);
    }


    public function facebook_settings_data()
    {
        $this->ajax_check();
        $search_value = $_POST['search']['value'];
        $display_columns = array("#","CHECKBOX",'id', 'app_name', 'api_id', 'api_secret', 'status', 'token_validity', 'action');
        $search_columns = array('app_name', 'api_id');

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 2;
        $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'id';
        $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'desc';
        $order_by=$sort." ".$order;

        $where_custom = '';
        $where_custom="user_id = ".$this->user_id;

        if ($search_value != '') 
        {
            foreach ($search_columns as $key => $value) 
            $temp[] = $value." LIKE "."'%$search_value%'";
            $imp = implode(" OR ", $temp);
            $where_custom .=" AND (".$imp.") ";
        }


        $table="facebook_rx_config";
        $this->db->where($where_custom);
        $info=$this->basic->get_data($table,$where='',$select='',$join='',$limit,$start,$order_by,$group_by='');
        $this->db->where($where_custom);
        $total_rows_array=$this->basic->count_row($table,$where='',$count=$table.".id",$join='',$group_by='');
        $total_result=$total_rows_array[0]['total_rows'];

        $i=0;
        $base_url=base_url();
        foreach ($info as $key => $value) 
        {
            if($this->is_demo == '1')
            $info[$i]['api_secret'] = "XXXXXXXXXXX";

            $token_validity = $this->facebookTokenValidityCheck($value['user_access_token']);
            if($value['status'] == 1)
                $info[$i]['status'] = "<span class='badge badge-status text-success'><i class='fa fa-check-circle green'></i> ".$this->lang->line('Active')."</span>";
            else
                $info[$i]['status'] = "<span class='badge badge-status text-danger'><i class='fa fa-check-circle red'></i> ".$this->lang->line('Inactive')."</span>";
            $info[$i]['token_validity'] = $token_validity;

            $info[$i]['action'] = "";
            
            if($this->is_demo != '1')
            $info[$i]['action'] .= "<div style='min-width:130px'><a href='".base_url('social_apps/edit_facebook_settings/').$value['id']."' class='btn btn-outline-warning btn-circle' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Edit APP Settings')."'><i class='fas fa-edit'></i></a> ";
            
            if($this->is_demo != '1')
            $info[$i]['action'] .= "<a href='".base_url('social_apps/login_button/').$value['id']."' class='btn btn-outline-primary btn-circle' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Login to validate your accesstoken.')."'><i class='fab fa-facebook-square'></i></a> <a href='#' class='btn btn-outline-danger btn-circle delete_app' table_id='".$value['id']."' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Delete this APP')."'><i class='fas fa-trash-alt'></i></a></div>";

            $info[$i]["action"] .="<script>$('[data-toggle=\"tooltip\"]').tooltip();</script>";
            $i++;
        }

        $data['draw'] = (int)$_POST['draw'] + 1;
        $data['recordsTotal'] = $total_result;
        $data['recordsFiltered'] = $total_result;
        $data['data'] = convertDataTableResult($info, $display_columns ,$start,$primary_key="id");

        echo json_encode($data);
    }


    public function add_facebook_settings()
    {
        $data['table_id'] = 0;
        $data['facebook_settings'] = array();
        $data['page_title'] = $this->lang->line('Facebook App Settings');
        $data['title'] = $this->lang->line('Facebook App Settings');
        $data['body'] = 'admin/social_apps/facebook_settings';

        $this->_viewcontroller($data);
    }


    public function edit_facebook_settings($table_id=0)
    {
        
        if($this->is_demo == '1')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
            exit();
        }
        if($table_id==0) exit;
        $facebook_settings = $this->basic->get_data('facebook_rx_config',array("where"=>array("id"=>$table_id)));
        if (!isset($facebook_settings[0])) $facebook_settings = array();
        else $facebook_settings = $facebook_settings[0];
        $data['table_id'] = $table_id;
        $data['facebook_settings'] = $facebook_settings;
        $data['page_title'] = $this->lang->line('Facebook App Settings');
        $data['title'] = $this->lang->line('Facebook App Settings');
        $data['body'] = 'admin/social_apps/facebook_settings';

        $this->_viewcontroller($data);
    }




    public function facebook_settings_update_action()
    {
        if($this->is_demo == '1')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
            exit();
        }

        if (!isset($_POST)) exit;



        $this->form_validation->set_rules('api_id', $this->lang->line("App ID"), 'trim|required');
        $this->form_validation->set_rules('api_secret', $this->lang->line("App Secret"), 'trim|required');
        $table_id = $this->input->post('table_id',true);

        if ($this->form_validation->run() == FALSE) 
        {
            if($table_id == 0) $this->add_facebook_settings();
            else $this->edit_facebook_settings($table_id);
        }
        else {

            $insert_data['app_name'] = $this->input->post('app_name');
            $insert_data['api_id'] = $this->input->post('api_id');
            $insert_data['api_secret'] = $this->input->post('api_secret');
            $insert_data['user_id'] = $this->user_id;

            if($this->session->userdata('user_type') == 'Admin')
                $insert_data['use_by'] = 'everyone';
            else
                $insert_data['use_by'] = 'only_me';
            
            $status = $this->input->post('status');
            if($status=='') $status='0';
            $insert_data['status'] = $status;

            $facebook_settings = $this->basic->get_data('facebook_rx_config');

            if ($table_id != 0) {
                $this->basic->update_data('facebook_rx_config', array('id' => $table_id,"user_id"=>$this->user_id), $insert_data);
            }
            else 
                $this->basic->insert_data('facebook_rx_config', $insert_data);

            $this->session->set_flashdata('success_message', '1');
            redirect(base_url('social_apps/facebook_settings'),'location');
            
        }
    }


    public function login_button($id)
    {     
        $fb_config_info = $this->basic->get_data('facebook_rx_config',array('where'=>array('id'=>$id)));
        if(isset($fb_config_info[0]['developer_access']) && $fb_config_info[0]['developer_access'] == '1' && $this->session->userdata('user_type')=="Admin")
        {
            $url = "https://ac.getapptoken.com/home/get_secret_code_info";
            $config_id = $fb_config_info[0]['secret_code'];

            $json="secret_code={$config_id}";
     
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch,CURLOPT_POSTFIELDS,$json);
            curl_setopt($ch,CURLOPT_POST,1);
            // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);  
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
            curl_setopt($ch, CURLOPT_COOKIEJAR,'cookie.txt');  
            curl_setopt($ch, CURLOPT_COOKIEFILE,'cookie.txt');  
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.3) Gecko/20070309 Firefox/2.0.0.3");
            $st=curl_exec($ch);  
            $result=json_decode($st,TRUE);


            if(isset($result['error']))
            {
                $this->session->set_userdata('secret_code_error','Invalid secret code!');
                redirect('facebook_rx_config/index','location');                
                exit();
            }


            // collect data from our server and then insert it into faceboo_rx_config_table and then call library for user and page info
            $config_data = array(
                'api_id' => $result['api_id'],
                'api_secret' => $result['api_secret'],
                'facebook_id' => $result['fb_id'],
                'user_access_token' => $result['access_token']
            );
            $this->basic->update_data("facebook_rx_config",array('id'=>$id),$config_data);

            $data = array(
                'user_id' => $this->user_id,
                'facebook_rx_config_id' => $id,
                'access_token' => $result['access_token'],
                'name' => isset($result['name']) ? $result['name'] : "",
                'email' => isset($result['email']) ? $result['email'] : "",
                'fb_id' => $result['fb_id'],
                'add_date' => date('Y-m-d')
                );

            $where=array();
            $where['where'] = array('user_id'=>$this->user_id,'fb_id'=>$result['fb_id']);
            $exist_or_not = $this->basic->get_data('facebook_rx_fb_user_info',$where);

            if(empty($exist_or_not))
            {
                $this->basic->insert_data('facebook_rx_fb_user_info',$data);
                $facebook_table_id = $this->db->insert_id();
            }
            else
            {
                $facebook_table_id = $exist_or_not[0]['id'];
                $where = array('user_id'=>$this->user_id,'fb_id'=>$result['fb_id']);
                $this->basic->update_data('facebook_rx_fb_user_info',$where,$data);
            }

            $this->session->set_userdata("facebook_rx_fb_user_info",$facebook_table_id);


            $this->session->set_userdata("fb_rx_login_database_id",$id);
            $this->fb_rx_login->app_initialize($id);
            $page_list = $this->fb_rx_login->get_page_list($result['access_token']);            
            if(!empty($page_list))
            {
                foreach($page_list as $page)
                {
                    $user_id = $this->user_id;
                    $page_id = $page['id'];
                    $page_cover = '';
                    if(isset($page['cover']['source'])) $page_cover = $page['cover']['source'];
                    $page_profile = '';
                    if(isset($page['picture']['url'])) $page_profile = $page['picture']['url'];
                    $page_name = '';
                    if(isset($page['name'])) $page_name = $page['name'];
                    $page_username = '';
                    if(isset($page['username'])) $page_username = $page['username'];
                    $page_access_token = '';
                    if(isset($page['access_token'])) $page_access_token = $page['access_token'];
                    $page_email = '';
                    if(isset($page['emails'][0])) $page_email = $page['emails'][0];

                    $data = array(
                        'user_id' => $user_id,
                        'facebook_rx_fb_user_info_id' => $facebook_table_id,
                        'page_id' => $page_id,
                        'page_cover' => $page_cover,
                        'page_profile' => $page_profile,
                        'page_name' => $page_name,
                        'username' => $page_username,
                        'page_access_token' => $page_access_token,
                        'page_email' => $page_email,
                        'add_date' => date('Y-m-d')
                        );

                    $where=array();
                    $where['where'] = array('facebook_rx_fb_user_info_id'=>$facebook_table_id,'page_id'=>$page['id']);
                    $exist_or_not = $this->basic->get_data('facebook_rx_fb_page_info',$where);

                    if(empty($exist_or_not))
                    {
                        $this->basic->insert_data('facebook_rx_fb_page_info',$data);
                    }
                    else
                    {
                        $where = array('facebook_rx_fb_user_info_id'=>$facebook_table_id,'page_id'=>$page['id']);
                        $this->basic->update_data('facebook_rx_fb_page_info',$where,$data);
                    }

                }
            }

            $group_list = $this->fb_rx_login->get_group_list($result['access_token']);

            if(!empty($group_list))
            {
                foreach($group_list as $group)
                {
                    $user_id = $this->user_id;
                    $group_access_token = $result['access_token']; // group uses user access token
                    $group_id = $group['id'];
                    $group_cover = '';
                    if(isset($group['cover']['source'])) $group_cover = $group['cover']['source'];
                    $group_profile = '';
                    if(isset($group['picture']['url'])) $group_profile = $group['picture']['url'];
                    $group_name = '';
                    if(isset($group['name'])) $group_name = $group['name'];

                    $data = array(
                        'user_id' => $user_id,
                        'facebook_rx_fb_user_info_id' => $facebook_table_id,
                        'group_id' => $group_id,
                        'group_cover' => $group_cover,
                        'group_profile' => $group_profile,
                        'group_name' => $group_name,
                        'group_access_token' => $group_access_token,
                        'add_date' => date('Y-m-d')
                        );

                    $where=array();
                    $where['where'] = array('facebook_rx_fb_user_info_id'=>$facebook_table_id,'group_id'=>$group['id']);
                    $exist_or_not = $this->basic->get_data('facebook_rx_fb_group_info',$where);

                    if(empty($exist_or_not))
                    {
                        $this->basic->insert_data('facebook_rx_fb_group_info',$data);
                    }
                    else
                    {
                        $where = array('facebook_rx_fb_user_info_id'=>$facebook_table_id,'group_id'=>$page['id']);
                        $this->basic->update_data('facebook_rx_fb_group_info',$where,$data);
                    }
                }
            }
            $this->session->set_userdata('success_message', 'success');
            redirect('facebook_rx_account_import/index','location');
        }
        else
        {
            $this->session->set_userdata("fb_rx_login_database_id",$id);
            $this->load->library('fb_rx_login');
            $redirect_url = base_url()."home/redirect_rx_link";        
            $data['fb_login_button'] = $this->fb_rx_login->login_for_user_access_token($redirect_url);  

            $data['body'] = 'facebook_rx/admin_login';
            $data['page_title'] =  $this->lang->line("Admin login");
            $data['expired_or_not'] = $this->fb_rx_login->access_token_validity_check();
            $this->_viewcontroller($data);
        }
    }





    /**
     * Twitter section starts
     */
    public function twitter_settings()
    {
        $data['page_title'] = $this->lang->line('Twitter App Settings');
        $data['title'] = $this->lang->line('Twitter App Settings');
        $data['body'] = 'admin/social_apps/twitter_app_settings';

        $this->_viewcontroller($data);
    }

    public function twitter_settings_data()
    {
        $this->ajax_check();
        $search_value = $_POST['search']['value'];
        $display_columns = array("#","CHECKBOX",'id', 'app_name', 'consumer_id', 'consumer_secret', 'status', 'action');
        $search_columns = array('app_name', 'consumer_id');

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 2;
        $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'id';
        $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'desc';
        $order_by=$sort." ".$order;

        $where_custom = '';
        $where_custom="user_id = ".$this->user_id;

        if ($search_value != '') 
        {
            foreach ($search_columns as $key => $value) 
            $temp[] = $value." LIKE "."'%$search_value%'";
            $imp = implode(" OR ", $temp);
            $where_custom .=" AND (".$imp.") ";
        }


        $table="twitter_config";
        $this->db->where($where_custom);
        $info=$this->basic->get_data($table,$where='',$select='',$join='',$limit,$start,$order_by,$group_by='');
        $this->db->where($where_custom);
        $total_rows_array=$this->basic->count_row($table,$where='',$count=$table.".id",$join='',$group_by='');
        $total_result=$total_rows_array[0]['total_rows'];

        $i=0;
        $base_url=base_url();
        foreach ($info as $key => $value) 
        {
            if($this->is_demo == '1')
            $info[$i]['client_secret'] = "XXXXXXXXXXX";

            if ($info[$i]['status'] == '1') {

                $info[$i]['status'] = '<span class="badge badge-status text-success"><i class="fa fa-check-circle green"></i> Active</span>';
            }
            else {

                $info[$i]['status'] = '<span class="badge badge-status text-danger"><i class="fa fa-check-circle red"></i> Inactive</span>';
            }


            $info[$i]['action'] = "";
            
            if($this->is_demo != '1')
            $info[$i]['action'] .= "<div style='min-width:130px'><a href='".base_url('social_apps/edit_twitter_settings/').$value['id']."' class='btn btn-outline-warning btn-circle' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Edit APP Settings')."'><i class='fas fa-edit'></i></a> ";
            
            if($this->is_demo != '1')
            $info[$i]['action'] .= "<a href='#'  table_id='".$value['id']."' class='btn btn-outline-primary btn-circle change_state' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Change the state of this app.')."'><i class='fas fa-exchange-alt'></i></a> <a href='#' class='btn btn-outline-danger btn-circle delete_app' table_id='".$value['id']."' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Delete this APP')."'><i class='fas fa-trash-alt'></i></a></div>";

            $info[$i]["action"] .="<script>$('[data-toggle=\"tooltip\"]').tooltip();</script>";
            $i++;
        }

        $data['draw'] = (int)$_POST['draw'] + 1;
        $data['recordsTotal'] = $total_result;
        $data['recordsFiltered'] = $total_result;
        $data['data'] = convertDataTableResult($info, $display_columns ,$start,$primary_key="id");

        echo json_encode($data);
    }


    public function add_twitter_settings()
    {
        $data['table_id'] = 0;
        $data['twitter_settings'] = array();
        $data['page_title'] = $this->lang->line('Twitter App Settings');
        $data['title'] = $this->lang->line('Twitter App Settings');
        $data['body'] = 'admin/social_apps/twitter_settings';

        $this->_viewcontroller($data);
    }


    public function edit_twitter_settings($table_id=0)
    {
        
        if($this->is_demo == '1')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
            exit();
        }
        if($table_id==0) exit;
        $twitter_settings = $this->basic->get_data('twitter_config',array("where"=>array("id"=>$table_id)));
        if (!isset($twitter_settings[0])) $twitter_settings = array();
        else $twitter_settings = $twitter_settings[0];
        $data['table_id'] = $table_id;
        $data['twitter_settings'] = $twitter_settings;
        $data['page_title'] = $this->lang->line('Twitter App Settings');
        $data['title'] = $this->lang->line('Twitter App Settings');
        $data['body'] = 'admin/social_apps/Twitter_settings';

        $this->_viewcontroller($data);
    }


    public function twitter_settings_update_action()
    {
        if($this->is_demo == '1')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
            exit();
        }

        if (!isset($_POST)) exit;


        $this->form_validation->set_rules('consumer_id', $this->lang->line("Consumer ID"), 'trim|required');
        $this->form_validation->set_rules('consumer_secret', $this->lang->line("Consumer Secret"), 'trim|required');
        $table_id = $this->input->post('table_id',true);

        if ($this->form_validation->run() == FALSE) 
        {
            if($table_id == 0) $this->add_twitter_settings();
            else $this->edit_twitter_settings($table_id);
        }
        else {

            $insert_data['app_name'] = $this->input->post('app_name');
            $insert_data['consumer_id'] = $this->input->post('consumer_id');
            $insert_data['consumer_secret'] = $this->input->post('consumer_secret');
            $insert_data['user_id'] = $this->user_id;

            
            $status = $this->input->post('status');
            if($status=='') $status='0';
            else {
                $this->basic->update_data('twitter_config', '', array('status' => '0'));
            }
            $insert_data['status'] = $status;

            $facebook_settings = $this->basic->get_data('twitter_config');

            if ($table_id != 0) {
                $this->basic->update_data('twitter_config', array('id' => $table_id,"user_id"=>$this->user_id), $insert_data);
            }
            else 
                $this->basic->insert_data('twitter_config', $insert_data);

            $this->session->set_flashdata('success_message', '1');
            redirect(base_url('social_apps/twitter_settings'),'location');
        }
    }


    /**
     *                  incomplete
     * complete it after importing user & campaign creating
     */
    public function delete_app_twitter()
    {
        $this->ajax_check();

        $app_table_id = $this->input->post('app_table_id', true);
        $this->basic->delete_data('twitter_config', array('id' => $app_table_id, 'user_id' => $this->user_id));
        echo json_encode(array('status' => '1', 'message' => $this->lang->line("App has deleted successfully.")));
    }


    public function change_app_status_twitter()
    {
        $this->ajax_check();

        $app_table_id = $this->input->post('app_table_id', true);
        $app_info = $this->basic->get_data('twitter_config', array('where' => array('id' => $app_table_id, 'user_id' => $this->user_id)));

        if (count($app_info) > 0) {

            if ($app_info[0]['status'] == '0') { 
                $status_to_be_changed = '1';
                $this->basic->update_data('twitter_config', '', array('status' => '0'));
            } else {
                $status_to_be_changed = '0';
            }

            $this->basic->update_data('twitter_config', array('id' => $app_table_id, 'user_id' => $this->user_id), array('status' => $status_to_be_changed));

            echo json_encode(array('status' => '1', 'message' => $this->lang->line("App status changed successfully.")));exit;
        } else {
            echo json_encode(array('status' => '0', 'message' => $this->lang->line("Sorry, no information is found for this app.")));
        }
    }
    /* Twitter section ends */




    /**
     * Linkedin section starts
     */
    public function linkedin_settings()
    {
        $data['page_title'] = $this->lang->line('Linkedin App Settings');
        $data['title'] = $this->lang->line('Linkedin App Settings');
        $data['body'] = 'admin/social_apps/linkedin_app_settings';

        $this->_viewcontroller($data);
    }


    public function linkedin_settings_data()
    {
        $this->ajax_check();
        $search_value = $_POST['search']['value'];
        $display_columns = array("#","CHECKBOX",'id', 'app_name', 'client_id', 'client_secret', 'status', 'action');
        $search_columns = array('app_name', 'client_id');

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 2;
        $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'id';
        $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'desc';
        $order_by=$sort." ".$order;

        $where_custom = '';
        $where_custom="user_id = ".$this->user_id;

        if ($search_value != '') 
        {
            foreach ($search_columns as $key => $value) 
            $temp[] = $value." LIKE "."'%$search_value%'";
            $imp = implode(" OR ", $temp);
            $where_custom .=" AND (".$imp.") ";
        }


        $table="linkedin_config";
        $this->db->where($where_custom);
        $info=$this->basic->get_data($table,$where='',$select='',$join='',$limit,$start,$order_by,$group_by='');
        $this->db->where($where_custom);
        $total_rows_array=$this->basic->count_row($table,$where='',$count=$table.".id",$join='',$group_by='');
        $total_result=$total_rows_array[0]['total_rows'];

        $i=0;
        $base_url=base_url();
        foreach ($info as $key => $value) 
        {
            if($this->is_demo == '1')
            $info[$i]['client_secret'] = "XXXXXXXXXXX";

            if ($info[$i]['status'] == '1') {

                $info[$i]['status'] = '<span class="badge badge-status text-success"><i class="fa fa-check-circle green"></i> Active</span>';
            }
            else {

                $info[$i]['status'] = '<span class="badge badge-status text-danger"><i class="fa fa-check-circle red"></i> Inactive</span>';
            }


            $info[$i]['action'] = "";
            
            if($this->is_demo != '1')
            $info[$i]['action'] .= "<div style='min-width:130px'><a href='".base_url('social_apps/edit_linkedin_settings/').$value['id']."' class='btn btn-outline-warning btn-circle' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Edit APP Settings')."'><i class='fas fa-edit'></i></a> ";
            
            if($this->is_demo != '1')
            $info[$i]['action'] .= "<a href='#'  table_id='".$value['id']."' class='btn btn-outline-primary btn-circle change_state' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Change the state of this app.')."'><i class='fas fa-exchange-alt'></i></a> <a href='#' class='btn btn-outline-danger btn-circle delete_app' table_id='".$value['id']."' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Delete this APP')."'><i class='fas fa-trash-alt'></i></a></div>";

            $info[$i]["action"] .="<script>$('[data-toggle=\"tooltip\"]').tooltip();</script>";
            $i++;
        }

        $data['draw'] = (int)$_POST['draw'] + 1;
        $data['recordsTotal'] = $total_result;
        $data['recordsFiltered'] = $total_result;
        $data['data'] = convertDataTableResult($info, $display_columns ,$start,$primary_key="id");

        echo json_encode($data);
    }


    public function add_linkedin_settings()
    {
        $data['table_id'] = 0;
        $data['linkedin_settings'] = array();
        $data['page_title'] = $this->lang->line('Linkedin App Settings');
        $data['title'] = $this->lang->line('Linkedin App Settings');
        $data['body'] = 'admin/social_apps/linkedin_settings';

        $this->_viewcontroller($data);
    }


    public function edit_linkedin_settings($table_id=0)
    {
        
        if($this->is_demo == '1')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
            exit();
        }
        if($table_id==0) exit;
        $linkedin_settings = $this->basic->get_data('linkedin_config',array("where"=>array("id"=>$table_id)));
        if (!isset($linkedin_settings[0])) $linkedin_settings = array();
        else $linkedin_settings = $linkedin_settings[0];
        $data['table_id'] = $table_id;
        $data['linkedin_settings'] = $linkedin_settings;
        $data['page_title'] = $this->lang->line('Linkedin App Settings');
        $data['title'] = $this->lang->line('Linkedin App Settings');
        $data['body'] = 'admin/social_apps/linkedin_settings';

        $this->_viewcontroller($data);
    }




    public function linkedin_settings_update_action()
    {
        if($this->is_demo == '1')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
            exit();
        }

        if (!isset($_POST)) exit;



        $this->form_validation->set_rules('client_id', $this->lang->line("App ID"), 'trim|required');
        $this->form_validation->set_rules('client_secret', $this->lang->line("App Secret"), 'trim|required');
        $table_id = $this->input->post('table_id',true);

        if ($this->form_validation->run() == FALSE) 
        {
            if($table_id == 0) $this->add_linkedin_settings();
            else $this->edit_linkedin_settings($table_id);
        }
        else {

            $insert_data['app_name'] = $this->input->post('app_name');
            $insert_data['client_id'] = $this->input->post('client_id');
            $insert_data['client_secret'] = $this->input->post('client_secret');
            $insert_data['user_id'] = $this->user_id;

            
            $status = $this->input->post('status');
            if($status=='') $status='0';
            else {
                $this->basic->update_data('linkedin_config', '', array('status' => '0'));
            }
            $insert_data['status'] = $status;

            $facebook_settings = $this->basic->get_data('linkedin_config');

            if ($table_id != 0) {
                $this->basic->update_data('linkedin_config', array('id' => $table_id,"user_id"=>$this->user_id), $insert_data);
            }
            else 
                $this->basic->insert_data('linkedin_config', $insert_data);

            $this->session->set_flashdata('success_message', '1');
            redirect(base_url('social_apps/linkedin_settings'),'location');
            
        }
    }


    /**
     *                  incomplete
     * complete it after importing user & campaign creating
     */
    public function delete_app_linkedin()
    {
        $this->ajax_check();

        $app_table_id = $this->input->post('app_table_id', true);
        $this->basic->delete_data('linkedin_config', array('id' => $app_table_id, 'user_id' => $this->user_id));
        echo json_encode(array('status' => '1', 'message' => $this->lang->line("App has deleted successfully.")));

    }


    public function change_app_status_linkedin()
    {
        $this->ajax_check();

        $app_table_id = $this->input->post('app_table_id', true);
        $app_info = $this->basic->get_data('linkedin_config', array('where' => array('id' => $app_table_id, 'user_id' => $this->user_id)));

        if (count($app_info) > 0) {

            if ($app_info[0]['status'] == '0') { 
                $status_to_be_changed = '1';
                $this->basic->update_data('linkedin_config', '', array('status' => '0'));
            } else {
                $status_to_be_changed = '0';
            }

            $this->basic->update_data('linkedin_config', array('id' => $app_table_id, 'user_id' => $this->user_id), array('status' => $status_to_be_changed));

            echo json_encode(array('status' => '1', 'message' => $this->lang->line("App status changed successfully.")));exit;
        } else {
            echo json_encode(array('status' => '0', 'message' => $this->lang->line("Sorry, no information is found for this app.")));
        }
    }
    /* Linkedin section ends */


    /**
     * Reddit section starts
     */
    public function reddit_settings()
    {
        $data['page_title'] = $this->lang->line('Reddit App Settings');
        $data['title'] = $this->lang->line('Reddit App Settings');
        $data['body'] = 'admin/social_apps/reddit_app_settings';

        $this->_viewcontroller($data);
    }


    public function reddit_settings_data()
    {
        $this->ajax_check();
        $search_value = $_POST['search']['value'];
        $display_columns = array("#","CHECKBOX",'id', 'app_name', 'client_id', 'client_secret', 'status', 'action');
        $search_columns = array('app_name', 'client_id');

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 2;
        $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'id';
        $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'desc';
        $order_by=$sort." ".$order;

        $where_custom = '';
        $where_custom="user_id = ".$this->user_id;

        if ($search_value != '') 
        {
            foreach ($search_columns as $key => $value) 
            $temp[] = $value." LIKE "."'%$search_value%'";
            $imp = implode(" OR ", $temp);
            $where_custom .=" AND (".$imp.") ";
        }


        $table="reddit_config";
        $this->db->where($where_custom);
        $info=$this->basic->get_data($table,$where='',$select='',$join='',$limit,$start,$order_by,$group_by='');
        $this->db->where($where_custom);
        $total_rows_array=$this->basic->count_row($table,$where='',$count=$table.".id",$join='',$group_by='');
        $total_result=$total_rows_array[0]['total_rows'];

        $i=0;
        $base_url=base_url();
        foreach ($info as $key => $value) 
        {
            if($this->is_demo == '1')
            $info[$i]['client_secret'] = "XXXXXXXXXXX";

            if ($info[$i]['status'] == '1') {

                $info[$i]['status'] = '<span class="badge badge-status text-success"><i class="fa fa-check-circle green"></i> Active</span>';
            }
            else {

                $info[$i]['status'] = '<span class="badge badge-status text-danger"><i class="fa fa-check-circle red"></i> Inactive</span>';
            }


            $info[$i]['action'] = "";
            
            if($this->is_demo != '1')
            $info[$i]['action'] .= "<div style='min-width:130px'><a href='".base_url('social_apps/edit_reddit_settings/').$value['id']."' class='btn btn-outline-warning btn-circle' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Edit APP Settings')."'><i class='fas fa-edit'></i></a> ";
            
            if($this->is_demo != '1')
            $info[$i]['action'] .= "<a href='#'  table_id='".$value['id']."' class='btn btn-outline-primary btn-circle change_state' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Change the state of this app.')."'><i class='fas fa-exchange-alt'></i></a> <a href='#' class='btn btn-outline-danger btn-circle delete_app' table_id='".$value['id']."' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Delete this APP')."'><i class='fas fa-trash-alt'></i></a></div>";

            $info[$i]["action"] .="<script>$('[data-toggle=\"tooltip\"]').tooltip();</script>";
            $i++;
        }

        $data['draw'] = (int)$_POST['draw'] + 1;
        $data['recordsTotal'] = $total_result;
        $data['recordsFiltered'] = $total_result;
        $data['data'] = convertDataTableResult($info, $display_columns ,$start,$primary_key="id");

        echo json_encode($data);
    }


    public function add_reddit_settings()
    {
        $data['table_id'] = 0;
        $data['reddit_settings'] = array();
        $data['page_title'] = $this->lang->line('Reddit App Settings');
        $data['title'] = $this->lang->line('Reddit App Settings');
        $data['body'] = 'admin/social_apps/reddit_settings';

        $this->_viewcontroller($data);
    }


    public function edit_reddit_settings($table_id=0)
    {
        
        if($this->is_demo == '1')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
            exit();
        }
        if($table_id==0) exit;
        $reddit_settings = $this->basic->get_data('reddit_config',array("where"=>array("id"=>$table_id)));
        if (!isset($reddit_settings[0])) $reddit_settings = array();
        else $reddit_settings = $reddit_settings[0];
        $data['table_id'] = $table_id;
        $data['reddit_settings'] = $reddit_settings;
        $data['page_title'] = $this->lang->line('Reddit App Settings');
        $data['title'] = $this->lang->line('Reddit App Settings');
        $data['body'] = 'admin/social_apps/reddit_settings';

        $this->_viewcontroller($data);
    }


    public function reddit_settings_update_action()
    {
        if($this->is_demo == '1')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
            exit();
        }

        if (!isset($_POST)) exit;



        $this->form_validation->set_rules('client_id', $this->lang->line("App ID"), 'trim|required');
        $this->form_validation->set_rules('client_secret', $this->lang->line("App Secret"), 'trim|required');
        $table_id = $this->input->post('table_id',true);

        if ($this->form_validation->run() == FALSE) 
        {
            if($table_id == 0) $this->add_reddit_settings();
            else $this->edit_reddit_settings($table_id);
        }
        else {

            $insert_data['app_name'] = $this->input->post('app_name');
            $insert_data['client_id'] = $this->input->post('client_id');
            $insert_data['client_secret'] = $this->input->post('client_secret');
            $insert_data['user_id'] = $this->user_id;

            
            $status = $this->input->post('status');
            if($status=='') $status='0';
            else {
                $this->basic->update_data('reddit_config', '', array('status' => '0'));
            }
            $insert_data['status'] = $status;

            $facebook_settings = $this->basic->get_data('reddit_config');

            if ($table_id != 0) {
                $this->basic->update_data('reddit_config', array('id' => $table_id,"user_id"=>$this->user_id), $insert_data);
            }
            else 
                $this->basic->insert_data('reddit_config', $insert_data);

            $this->session->set_flashdata('success_message', '1');
            redirect(base_url('social_apps/reddit_settings'),'location');
            
        }
    }


    /**
     *                  incomplete
     * complete it after importing user & campaign creating
     */
    public function delete_app_reddit()
    {
        $this->ajax_check();

        $app_table_id = $this->input->post('app_table_id', true);
        $this->basic->delete_data('reddit_config', array('id' => $app_table_id, 'user_id' => $this->user_id));
        echo json_encode(array('status' => '1', 'message' => $this->lang->line("App has deleted successfully.")));

    }


    public function change_app_status_reddit()
    {
        $this->ajax_check();

        $app_table_id = $this->input->post('app_table_id', true);
        $app_info = $this->basic->get_data('reddit_config', array('where' => array('id' => $app_table_id, 'user_id' => $this->user_id)));

        if (count($app_info) > 0) {

            if ($app_info[0]['status'] == '0') { 
                $status_to_be_changed = '1';
                $this->basic->update_data('reddit_config', '', array('status' => '0'));
            } else {
                $status_to_be_changed = '0';
            }

            $this->basic->update_data('reddit_config', array('id' => $app_table_id, 'user_id' => $this->user_id), array('status' => $status_to_be_changed));

            echo json_encode(array('status' => '1', 'message' => $this->lang->line("App status changed successfully.")));exit;
        } else {
            echo json_encode(array('status' => '0', 'message' => $this->lang->line("Sorry, no information is found for this app.")));
        }
    }
    /* Reddit section ends */


    /**
     * Pinterest section starts
     */
    public function pinterest_settings()
    {
        $data['page_title'] = $this->lang->line('Pinterest App Settings');
        $data['title'] = $this->lang->line('Pinterest App Settings');
        $data['body'] = 'admin/social_apps/pinterest_app_settings';

        $this->_viewcontroller($data);
    }


    public function pinterest_settings_data()
    {
        $this->ajax_check();
        $search_value = $_POST['search']['value'];
        $display_columns = array("#","CHECKBOX",'id', 'app_name', 'client_id', 'client_secret', 'status', 'action');
        $search_columns = array('app_name', 'client_id');

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 2;
        $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'id';
        $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'desc';
        $order_by=$sort." ".$order;

        $where_custom = '';
        $where_custom="user_id = ".$this->user_id;

        if ($search_value != '') 
        {
            foreach ($search_columns as $key => $value) 
            $temp[] = $value." LIKE "."'%$search_value%'";
            $imp = implode(" OR ", $temp);
            $where_custom .=" AND (".$imp.") ";
        }


        $table="pinterest_config";
        $this->db->where($where_custom);
        $info=$this->basic->get_data($table,$where='',$select='',$join='',$limit,$start,$order_by,$group_by='');
        $this->db->where($where_custom);
        $total_rows_array=$this->basic->count_row($table,$where='',$count=$table.".id",$join='',$group_by='');
        $total_result=$total_rows_array[0]['total_rows'];

        $i=0;
        $base_url=base_url();
        foreach ($info as $key => $value) 
        {
            if($this->is_demo == '1')
            $info[$i]['client_secret'] = "XXXXXXXXXXX";

            if ($info[$i]['status'] == '1') {

                $info[$i]['status'] = '<span class="badge badge-status text-success"><i class="fa fa-check-circle green"></i> Active</span>';
            }
            else {

                $info[$i]['status'] = '<span class="badge badge-status text-danger"><i class="fa fa-check-circle red"></i> Inactive</span>';
            }


            $login_button = base_url('social_apps/pinterest_intermediate_account_import_page/'.$info[$i]['id']);


            $info[$i]['action'] = "";
            
            if($this->is_demo != '1')
            $info[$i]['action'] .= "<div style='min-width:171px'><a href='".base_url('social_apps/edit_pinterest_settings/').$value['id']."' class='btn btn-outline-warning btn-circle' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Edit APP Settings')."'><i class='fas fa-edit'></i></a> ";
            
            if($this->is_demo != '1')
            $info[$i]['action'] .= "<a href='#'  table_id='".$value['id']."' class='btn btn-outline-primary btn-circle change_state' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Change the state of this app.')."'><i class='fas fa-exchange-alt'></i></a> <a href='#' class='btn btn-outline-danger btn-circle delete_app' table_id='".$value['id']."' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Delete this APP')."'><i class='fas fa-trash-alt'></i></a> <a href='". $login_button ."' class='btn btn-outline-info btn-circle ' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Import Account')."'><i class='fab fa-pinterest-square'></i></a></div>";

            $info[$i]["action"] .="<script>$('[data-toggle=\"tooltip\"]').tooltip();</script>";
            $i++;
        }

        $data['draw'] = (int)$_POST['draw'] + 1;
        $data['recordsTotal'] = $total_result;
        $data['recordsFiltered'] = $total_result;
        $data['data'] = convertDataTableResult($info, $display_columns ,$start,$primary_key="id");

        echo json_encode($data);
    }


    public function pinterest_intermediate_account_import_page($app_id)
    {
        $this->session->set_userdata('pinterest_app_id', $app_id);

        $this->load->library('Pinterests');

        $this->pinterests->app_initialize($app_id);
        $redirect_url = base_url('comboposter/login_callback/pinterest');
        $login_url = $this->pinterests->login_button($redirect_url);

        redirect($login_url,'location');
    }


    public function add_pinterest_settings()
    {
        $data['table_id'] = 0;
        $data['pinterest_settings'] = array();
        $data['page_title'] = $this->lang->line('pinterest App Settings');
        $data['title'] = $this->lang->line('pinterest App Settings');
        $data['body'] = 'admin/social_apps/pinterest_settings';

        $this->_viewcontroller($data);
    }


    public function edit_pinterest_settings($table_id=0)
    {
        
        if($this->is_demo == '1')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
            exit();
        }
        if($table_id==0) exit;
        $pinterest_settings = $this->basic->get_data('pinterest_config',array("where"=>array("id"=>$table_id)));
        if (!isset($pinterest_settings[0])) $pinterest_settings = array();
        else $pinterest_settings = $pinterest_settings[0];
        $data['table_id'] = $table_id;
        $data['pinterest_settings'] = $pinterest_settings;
        $data['page_title'] = $this->lang->line('Pinterest App Settings');
        $data['title'] = $this->lang->line('Pinterest App Settings');
        $data['body'] = 'admin/social_apps/pinterest_settings';

        $this->_viewcontroller($data);
    }




    public function pinterest_settings_update_action()
    {
        if($this->is_demo == '1')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
            exit();
        }

        if (!isset($_POST)) exit;


        $this->form_validation->set_rules('client_id', $this->lang->line("App ID"), 'trim|required');
        $this->form_validation->set_rules('client_secret', $this->lang->line("App Secret"), 'trim|required');
        $table_id = $this->input->post('table_id',true);

        if ($this->form_validation->run() == FALSE) 
        {
            if($table_id == 0) $this->add_pinterest_settings();
            else $this->edit_pinterest_settings($table_id);
        }
        else {

            $insert_data['app_name'] = $this->input->post('app_name');
            $insert_data['client_id'] = $this->input->post('client_id');
            $insert_data['client_secret'] = $this->input->post('client_secret');
            $insert_data['user_id'] = $this->user_id;

            
            $status = $this->input->post('status');
            if($status=='') $status='0';
            // if ($status == '1') {
            //     $this->basic->update_data('pinterest_config', array('user_id' => $this->user_id), array('status' => '0'));
            // }

            $insert_data['status'] = $status;
            $facebook_settings = $this->basic->get_data('pinterest_config');

            if ($table_id != 0) {
                $this->basic->update_data('pinterest_config', array('id' => $table_id,"user_id"=>$this->user_id), $insert_data);
            }
            else 
                $this->basic->insert_data('pinterest_config', $insert_data);

            $this->session->set_flashdata('success_message', '1');
            redirect(base_url('social_apps/pinterest_settings'),'location');
            
        }
    }


    /**
     *                  incomplete
     * complete it after importing user & campaign creating
     */
    public function delete_app_pinterest()
    {
        $this->ajax_check();

        $app_table_id = $this->input->post('app_table_id', true);

        $this->basic->delete_data('pinterest_users_info', array('pinterest_config_table_id' => $app_table_id, 'user_id' => $this->user_id));

        $this->basic->delete_data('pinterest_config', array('id' => $app_table_id, 'user_id' => $this->user_id));
        
        echo json_encode(array('status' => '1', 'message' => $this->lang->line("App has deleted successfully.")));

    }


    public function change_app_status_pinterest()
    {
        $this->ajax_check();

        $app_table_id = $this->input->post('app_table_id', true);
        $app_info = $this->basic->get_data('pinterest_config', array('where' => array('id' => $app_table_id, 'user_id' => $this->user_id)));

        if (count($app_info) > 0) {

            if ($app_info[0]['status'] == '0') { 
                $status_to_be_changed = '1';
                // $this->basic->update_data('pinterest_config', '', array('status' => '0'));
            } else {
                $status_to_be_changed = '0';
            }

            $this->basic->update_data('pinterest_config', array('id' => $app_table_id, 'user_id' => $this->user_id), array('status' => $status_to_be_changed));

            echo json_encode(array('status' => '1', 'message' => $this->lang->line("App status changed successfully.")));exit;
        } else {
            echo json_encode(array('status' => '0', 'message' => $this->lang->line("Sorry, no information is found for this app.")));
        }
    }

    /* Pinterest section ends */
    

    /**
     * Wordpress section starts
     */
    public function wordpress_settings()
    {
        $data['page_title'] = $this->lang->line('Wordpress App Settings');
        $data['title'] = $this->lang->line('Wordpress App Settings');
        $data['body'] = 'admin/social_apps/wordpress_app_settings';

        $this->_viewcontroller($data);
    }


    public function wordpress_settings_data()
    {
        $this->ajax_check();
        $search_value = $_POST['search']['value'];
        $display_columns = array("#","CHECKBOX",'id', 'app_name', 'client_id', 'client_secret', 'status', 'action');
        $search_columns = array('app_name', 'client_id');

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 2;
        $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'id';
        $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'desc';
        $order_by=$sort." ".$order;

        $where_custom = '';
        $where_custom="user_id = ".$this->user_id;

        if ($search_value != '') 
        {
            foreach ($search_columns as $key => $value) 
            $temp[] = $value." LIKE "."'%$search_value%'";
            $imp = implode(" OR ", $temp);
            $where_custom .=" AND (".$imp.") ";
        }


        $table="wordpress_config";
        $this->db->where($where_custom);
        $info=$this->basic->get_data($table,$where='',$select='',$join='',$limit,$start,$order_by,$group_by='');
        $this->db->where($where_custom);
        $total_rows_array=$this->basic->count_row($table,$where='',$count=$table.".id",$join='',$group_by='');
        $total_result=$total_rows_array[0]['total_rows'];

        $i=0;
        $base_url=base_url();
        foreach ($info as $key => $value) 
        {
            if($this->is_demo == '1')
            $info[$i]['client_secret'] = "XXXXXXXXXXX";

            if ($info[$i]['status'] == '1') {

                $info[$i]['status'] = '<span class="badge badge-status text-success"><i class="fa fa-check-circle green"></i> Active</span>';
            }
            else {

                $info[$i]['status'] = '<span class="badge badge-status text-danger"><i class="fa fa-check-circle red"></i> Inactive</span>';
            }


            $info[$i]['action'] = "";
            
            if($this->is_demo != '1')
            $info[$i]['action'] .= "<div style='min-width:130px'><a href='".base_url('social_apps/edit_wordpress_settings/').$value['id']."' class='btn btn-outline-warning btn-circle' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Edit APP Settings')."'><i class='fas fa-edit'></i></a> ";
            
            if($this->is_demo != '1')
            $info[$i]['action'] .= "<a href='#'  table_id='".$value['id']."' class='btn btn-outline-primary btn-circle change_state' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Change the state of this app.')."'><i class='fas fa-exchange-alt'></i></a> <a href='#' class='btn btn-outline-danger btn-circle delete_app' table_id='".$value['id']."' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Delete this APP')."'><i class='fas fa-trash-alt'></i></a></div>";

            $info[$i]["action"] .="<script>$('[data-toggle=\"tooltip\"]').tooltip();</script>";
            $i++;
        }

        $data['draw'] = (int)$_POST['draw'] + 1;
        $data['recordsTotal'] = $total_result;
        $data['recordsFiltered'] = $total_result;
        $data['data'] = convertDataTableResult($info, $display_columns ,$start,$primary_key="id");

        echo json_encode($data);
    }


    public function add_wordpress_settings()
    {
        $data['table_id'] = 0;
        $data['wordpress_settings'] = array();
        $data['page_title'] = $this->lang->line('Wordpress App Settings');
        $data['title'] = $this->lang->line('Wordpress App Settings');
        $data['body'] = 'admin/social_apps/wordpress_settings';

        $this->_viewcontroller($data);
    }


    public function edit_wordpress_settings($table_id=0)
    {
        
        if($this->is_demo == '1')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
            exit();
        }
        if($table_id==0) exit;
        $wordpress_settings = $this->basic->get_data('wordpress_config',array("where"=>array("id"=>$table_id)));
        if (!isset($wordpress_settings[0])) $wordpress_settings = array();
        else $wordpress_settings = $wordpress_settings[0];
        $data['table_id'] = $table_id;
        $data['wordpress_settings'] = $wordpress_settings;
        $data['page_title'] = $this->lang->line('Wordpress App Settings');
        $data['title'] = $this->lang->line('Wordpress App Settings');
        $data['body'] = 'admin/social_apps/wordpress_settings';

        $this->_viewcontroller($data);
    }


    public function wordpress_settings_update_action()
    {
        if($this->is_demo == '1')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
            exit();
        }

        if (!isset($_POST)) exit;



        $this->form_validation->set_rules('client_id', $this->lang->line("App ID"), 'trim|required');
        $this->form_validation->set_rules('client_secret', $this->lang->line("App Secret"), 'trim|required');
        $table_id = $this->input->post('table_id',true);

        if ($this->form_validation->run() == FALSE) 
        {
            if($table_id == 0) $this->add_wordpress_settings();
            else $this->edit_wordpress_settings($table_id);
        }
        else {

            $insert_data['app_name'] = $this->input->post('app_name');
            $insert_data['client_id'] = $this->input->post('client_id');
            $insert_data['client_secret'] = $this->input->post('client_secret');
            $insert_data['user_id'] = $this->user_id;

            
            $status = $this->input->post('status');
            if($status=='') $status='0';
            else {
                $this->basic->update_data('wordpress_config', '', array('status' => '0'));
            }
            $insert_data['status'] = $status;

            $facebook_settings = $this->basic->get_data('wordpress_config');

            if ($table_id != 0) {
                $this->basic->update_data('wordpress_config', array('id' => $table_id,"user_id"=>$this->user_id), $insert_data);
            }
            else 
                $this->basic->insert_data('wordpress_config', $insert_data);

            $this->session->set_flashdata('success_message', '1');
            redirect(base_url('social_apps/wordpress_settings'),'location');
            
        }
    }

    /**
     * Wordpress (Self-Hosted) section starts here
     */
    public function wordpress_settings_self_hosted()
    {
        $data['page_title'] = $this->lang->line('Wordpress settings (self-hosted)');
        $data['body'] = 'admin/social_apps/wordpress_app_settings_self_hosted';

        $this->_viewcontroller($data);
    }

    public function wordpress_settings_self_hosted_data()
    {
        $this->ajax_check();
        $search_value = $_POST['search']['value'];
        $display_columns = array('id', 'api_base_url', 'category', 'account_name', 'created_at');
        $search_columns = array('api_base_url', 'category', 'account_name');

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;

        $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 1;
        $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'id';
        $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'desc';
        $order_by = $sort . " " . $order;

        $where_custom = '';
        $where_custom="user_id = " . $this->user_id;

        if ($search_value != '') {
            foreach ($search_columns as $key => $value) {
                $temp[] = $value." LIKE "."'%$search_value%'";
            }

            $imp = implode(" OR ", $temp);
            $where_custom .= " AND (" . $imp . ") ";
        }


        $table="wordpress_config_self_hosted";
        $select = [
            'id',
            'api_base_url',
            'category',
            'account_name',
            'created_at'
        ];
        $this->db->where($where_custom);
        $info=$this->basic->get_data($table, $where='', $select, $join='', $limit, $start, $order_by, $group_by='');

        $this->db->where($where_custom);
        $total_rows_array=$this->basic->count_row($table,$where='',$count=$table.".id",$join='',$group_by='');
        $total_result=$total_rows_array[0]['total_rows'];

        $length = count($info);
        for ($i = 0; $i < $length; $i++) {

            if (isset($info[$i]['created_at'])) {
                $info[$i]['created_at'] = date('jS M Y H:i', strtotime($info[$i]['created_at']));
            }


            if (!isset($info[$i]['actions'])) {

                // Prepares buttons
                $actions = '<a data-toggle="tooltip" title="' . $this->lang->line('Edit form') . '" href="' . base_url("social_apps/edit_wordpress_settings_self_hosted/{$info[$i]['id']}") . '" class="btn btn-circle btn-outline-warning"><i class="fa fa-edit"></i></a>';
                $actions .= '&nbsp;&nbsp;<a data-toggle="tooltip" title="' . $this->lang->line('Delete form') . '" href="" class="btn btn-circle btn-outline-danger" id="delete-webview-form" data-form-id="'. $info[$i]['id'] . '"><i class="fas fa-trash-alt"></i></a>';

                $info[$i]['actions'] = $actions;               
            }

        }

        $data['draw'] = (int)$_POST['draw'] + 1;
        $data['recordsTotal'] = $total_result;
        $data['recordsFiltered'] = $total_result;
        $data['data'] = $info;

        echo json_encode($data);
    }

    public function add_wordpress_settings_self_hosted()
    {
        $data['page_title'] = $this->lang->line('Add Wordpress Settings (Self-Hosted)');
        $data['body'] = 'admin/social_apps/add_wordpress_settings_self_hosted';

        if ($_POST) {
            // Sets validation rules
            $this->form_validation->set_rules('api_base_url', $this->lang->line('API base URL'), 'trim|required');
            $this->form_validation->set_rules('account_name', $this->lang->line('Account name'), 'trim|required');
            $this->form_validation->set_rules('category', $this->lang->line('Category name'), 'trim|required');
            // $this->form_validation->set_rules('status', $this->lang->line('Account active status'), 'trim|required');

            $status = filter_var($this->input->post('status'), FILTER_SANITIZE_STRING);

            if (false === $this->form_validation->run()) {
                return $this->_viewcontroller($data);
            }

            $api_base_url = filter_var($this->input->post('api_base_url'), FILTER_VALIDATE_URL);
            $account_name = filter_var($this->input->post('account_name'), FILTER_SANITIZE_STRING);
            $category = filter_var($this->input->post('category'), FILTER_SANITIZE_STRING);
            $status = filter_var($this->input->post('status'), FILTER_SANITIZE_STRING);

            $data = [
                'api_base_url' => $api_base_url,
                'account_name' => $account_name,
                'category' => $category,
                'user_id' => $this->user_id,
                'status' => empty($status) ? '0' : '1',
                'created_at' => date('Y-m-d H:i:s'),
            ];

            if ($this->basic->insert_data('wordpress_config_self_hosted', $data)) {
                redirect('social_apps/wordpress_settings_self_hosted');
            }

            $message = $this->lang->line('Something went wrong while adding your wordpress site.');
            $this->session->set_userdata('add_wordpress_settings_self_hosted_error', $message);   
            return $this->_viewcontroller($data);
        }

        $this->_viewcontroller($data);
    }

    public function edit_wordpress_settings_self_hosted($id = null)
    {
    
    }

    /**
     *                  incomplete
     * complete it after importing user & campaign creating
     */
    public function delete_app_wordpress()
    {
        $this->ajax_check();

        $app_table_id = $this->input->post('app_table_id', true);
        $this->basic->delete_data('wordpress_config', array('id' => $app_table_id, 'user_id' => $this->user_id));
        echo json_encode(array('status' => '1', 'message' => $this->lang->line("App has deleted successfully.")));

    }


    public function change_app_status_wordpress()
    {
        $this->ajax_check();

        $app_table_id = $this->input->post('app_table_id', true);
        $app_info = $this->basic->get_data('wordpress_config', array('where' => array('id' => $app_table_id, 'user_id' => $this->user_id)));

        if (count($app_info) > 0) {

            if ($app_info[0]['status'] == '0') { 
                $status_to_be_changed = '1';
                $this->basic->update_data('wordpress_config', '', array('status' => '0'));
            } else {
                $status_to_be_changed = '0';
            }

            $this->basic->update_data('wordpress_config', array('id' => $app_table_id, 'user_id' => $this->user_id), array('status' => $status_to_be_changed));

            echo json_encode(array('status' => '1', 'message' => $this->lang->line("App status changed successfully.")));exit;
        } else {
            echo json_encode(array('status' => '0', 'message' => $this->lang->line("Sorry, no information is found for this app.")));
        }
    }
    /* Wordpress section ends */


    /**
     * Tumblr section starts
     */
    public function tumblr_settings()
    {
        $data['page_title'] = $this->lang->line('Tumblr App Settings');
        $data['title'] = $this->lang->line('Tumblr App Settings');
        $data['body'] = 'admin/social_apps/tumblr_app_settings';

        $this->_viewcontroller($data);
    }


    public function tumblr_settings_data()
    {
        $this->ajax_check();
        $search_value = $_POST['search']['value'];
        $display_columns = array("#","CHECKBOX",'id', 'app_name', 'consumer_id', 'consumer_secret', 'status', 'action');
        $search_columns = array('app_name', 'consumer_id');

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 2;
        $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'id';
        $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'desc';
        $order_by=$sort." ".$order;

        $where_custom = '';
        $where_custom="user_id = ".$this->user_id;

        if ($search_value != '') 
        {
            foreach ($search_columns as $key => $value) 
            $temp[] = $value." LIKE "."'%$search_value%'";
            $imp = implode(" OR ", $temp);
            $where_custom .=" AND (".$imp.") ";
        }


        $table="tumblr_config";
        $this->db->where($where_custom);
        $info=$this->basic->get_data($table,$where='',$select='',$join='',$limit,$start,$order_by,$group_by='');
        $this->db->where($where_custom);
        $total_rows_array=$this->basic->count_row($table,$where='',$count=$table.".id",$join='',$group_by='');
        $total_result=$total_rows_array[0]['total_rows'];

        $i=0;
        $base_url=base_url();
        foreach ($info as $key => $value) 
        {
            if($this->is_demo == '1')
            $info[$i]['client_secret'] = "XXXXXXXXXXX";

            if ($info[$i]['status'] == '1') {

                $info[$i]['status'] = '<span class="badge badge-status text-success"><i class="fa fa-check-circle green"></i> Active</span>';
            }
            else {

                $info[$i]['status'] = '<span class="badge badge-status text-danger"><i class="fa fa-check-circle red"></i> Inactive</span>';
            }


            $info[$i]['action'] = "";
            
            if($this->is_demo != '1')
            $info[$i]['action'] .= "<div style='min-width:130px'><a href='".base_url('social_apps/edit_tumblr_settings/').$value['id']."' class='btn btn-outline-warning btn-circle' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Edit APP Settings')."'><i class='fas fa-edit'></i></a> ";
            
            if($this->is_demo != '1')
            $info[$i]['action'] .= "<a href='#'  table_id='".$value['id']."' class='btn btn-outline-primary btn-circle change_state' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Change the state of this app.')."'><i class='fas fa-exchange-alt'></i></a> <a href='#' class='btn btn-outline-danger btn-circle delete_app' table_id='".$value['id']."' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Delete this APP')."'><i class='fas fa-trash-alt'></i></a></div>";

            $info[$i]["action"] .="<script>$('[data-toggle=\"tooltip\"]').tooltip();</script>";
            $i++;
        }

        $data['draw'] = (int)$_POST['draw'] + 1;
        $data['recordsTotal'] = $total_result;
        $data['recordsFiltered'] = $total_result;
        $data['data'] = convertDataTableResult($info, $display_columns ,$start,$primary_key="id");

        echo json_encode($data);
    }


    public function add_tumblr_settings()
    {
        $data['table_id'] = 0;
        $data['tumblr_settings'] = array();
        $data['page_title'] = $this->lang->line('Tumblr App Settings');
        $data['title'] = $this->lang->line('Tumblr App Settings');
        $data['body'] = 'admin/social_apps/tumblr_settings';

        $this->_viewcontroller($data);
    }


    public function edit_tumblr_settings($table_id=0)
    {
        
        if($this->is_demo == '1')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
            exit();
        }
        if($table_id==0) exit;
        $tumblr_settings = $this->basic->get_data('tumblr_config',array("where"=>array("id"=>$table_id)));
        if (!isset($tumblr_settings[0])) $tumblr_settings = array();
        else $tumblr_settings = $tumblr_settings[0];
        $data['table_id'] = $table_id;
        $data['tumblr_settings'] = $tumblr_settings;
        $data['page_title'] = $this->lang->line('tumblr App Settings');
        $data['title'] = $this->lang->line('tumblr App Settings');
        $data['body'] = 'admin/social_apps/tumblr_settings';

        $this->_viewcontroller($data);
    }


    public function tumblr_settings_update_action()
    {
        if($this->is_demo == '1')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
            exit();
        }

        if (!isset($_POST)) exit;


        $this->form_validation->set_rules('consumer_id', $this->lang->line("Consumer ID"), 'trim|required');
        $this->form_validation->set_rules('consumer_secret', $this->lang->line("Consumer Secret"), 'trim|required');
        $table_id = $this->input->post('table_id',true);

        if ($this->form_validation->run() == FALSE) 
        {
            if($table_id == 0) $this->add_tumblr_settings();
            else $this->edit_tumblr_settings($table_id);
        }
        else {

            $insert_data['app_name'] = $this->input->post('app_name');
            $insert_data['consumer_id'] = $this->input->post('consumer_id');
            $insert_data['consumer_secret'] = $this->input->post('consumer_secret');
            $insert_data['user_id'] = $this->user_id;

            
            $status = $this->input->post('status');
            if($status=='') $status='0';
            else {
                $this->basic->update_data('tumblr_config', '', array('status' => '0'));
            }
            $insert_data['status'] = $status;

            $facebook_settings = $this->basic->get_data('tumblr_config');

            if ($table_id != 0) {
                $this->basic->update_data('tumblr_config', array('id' => $table_id,"user_id"=>$this->user_id), $insert_data);
            }
            else 
                $this->basic->insert_data('tumblr_config', $insert_data);

            $this->session->set_flashdata('success_message', '1');
            redirect(base_url('social_apps/tumblr_settings'),'location');
        }
    }


    /**
     *                  incomplete
     * complete it after importing user & campaign creating
     */
    public function delete_app_tumblr()
    {
        $this->ajax_check();

        $app_table_id = $this->input->post('app_table_id', true);
        $this->basic->delete_data('tumblr_config', array('id' => $app_table_id, 'user_id' => $this->user_id));
        echo json_encode(array('status' => '1', 'message' => $this->lang->line("App has deleted successfully.")));
    }


    public function change_app_status_tumblr()
    {
        $this->ajax_check();

        $app_table_id = $this->input->post('app_table_id', true);
        $app_info = $this->basic->get_data('tumblr_config', array('where' => array('id' => $app_table_id, 'user_id' => $this->user_id)));

        if (count($app_info) > 0) {

            if ($app_info[0]['status'] == '0') { 
                $status_to_be_changed = '1';
                $this->basic->update_data('tumblr_config', '', array('status' => '0'));
            } else {
                $status_to_be_changed = '0';
            }

            $this->basic->update_data('tumblr_config', array('id' => $app_table_id, 'user_id' => $this->user_id), array('status' => $status_to_be_changed));

            echo json_encode(array('status' => '1', 'message' => $this->lang->line("App status changed successfully.")));exit;
        } else {
            echo json_encode(array('status' => '0', 'message' => $this->lang->line("Sorry, no information is found for this app.")));
        }
    }
    /* Tumblr section ends ffghfgh*/



}

