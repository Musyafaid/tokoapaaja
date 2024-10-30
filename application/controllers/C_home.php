<?php
class C_home extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('M_product');
        $this->load->library('pagination');   
        $this->load->library('encryption');   
    }

    public function index($offset = 0) {

        $search = $this->input->get('search');

          
        if(empty($this->M_product->get_category())){
            echo "Gagal mendapatkan category";
        }
        
        $data['categories'] = $this->M_product->get_category();

        if($this->input->get('search')){
                
           
            $config['total_rows'] = $this->M_product->count_all_products_is_sale($search);
            $config['reuse_query_string'] = TRUE;
        }else{          
            $config['total_rows'] = $this->M_product->count_all_products_is_sale();
        }
        
        $config['base_url'] = base_url('home');
        $config['per_page'] = 8;
        $config['uri_segment'] = 2;  
        
        $config['full_tag_open'] = '<ul class="pagination justify-content-center">';
        $config['full_tag_close'] = '</ul>';
        
        $config['first_link'] = 'First';
        $config['first_tag_open'] = '<li class="page-item">';
        $config['first_tag_close'] = '</li>';
        
        $config['last_link'] = 'Last';
        $config['last_tag_open'] = '<li class="page-item">';
        $config['last_tag_close'] = '</li>';
        
        $config['next_link'] = '&raquo';
        $config['next_tag_open'] = '<li class="page-item">';
        $config['next_tag_close'] = '</li>';
        
        $config['prev_link'] = '&laquo';
        $config['prev_tag_open'] = '<li class="page-item">';
        $config['prev_tag_close'] = '</li>';
        
        $config['cur_tag_open'] = '<li class="page-item active"><a class="page-link" href="#">';
        $config['cur_tag_close'] = '</a></li>';
        
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';
        
        $config['attributes'] = array('class' => 'page-link');
        
        $this->pagination->initialize($config);

        
        $data['navigation'] = "";
        if($this->input->get('search')){
            $data['products'] = $this->M_product->get_products_is_sale($config['per_page'], $offset,$this->input->get('search'));
            $data['navigation'] = "Back to all product";
        }else{
            $data['products'] = $this->M_product->get_products_is_sale($config['per_page'], $offset);
            $data['products_is_sale'] = $this->M_product->get_products($config['per_page'], $offset);
        }
        
        $data['pagination'] = $this->pagination->create_links();

        foreach($data['products'] as &$product){
            $product['product_id'] = $this->encrypt_id($product['product_id']);
        }

       

        $this->load->view('template/header');
        $this->load->view('page/home/header');
        $this->load->view('components/ui/navbar_user');
        if(empty($this->input->get('search'))){
        $this->load->view('components/content/jumbotron_user');
        }
        $this->load->view('components/content/product_list', $data);
        $this->load->view('components/ui/alert');

        $this->load->view('page/home/footer');
        
        $this->load->view('template/footer');
    }

    private function encrypt_id($id) {
        return $this->encryption->encrypt($id);
    }
    private function decrypt_id($id) {
        return $this->encryption->decrypt($id);
    }
    
    public function detail_product() {

        $input_id = $this->input->get('detail');

        $decrypt_id = $this->decrypt_id($input_id);

        if($decrypt_id == false){
            redirect('');
            return;
        }else{

            $data['products']= $this->M_product->get_products_is_sale_by_id($decrypt_id);

            if($data['products']){
                
                $this->load->view('template/header');
                $this->load->view('page/home/header');
                $this->load->view('components/ui/navbar_user');
                $this->load->view('components/content/detail_product',$data);
                $this->load->view('components/ui/alert');
                $this->load->view('page/home/footer');  
                $this->load->view('template/footer');
            }else{
                $this->session->set_flashdata('alertError','Failed To Fetch Product');
            }
            return;
        }
        
    }
    
}