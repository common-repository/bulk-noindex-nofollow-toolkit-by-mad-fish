<?php 

if( is_admin() && !class_exists( 'WP_List_Table' ) )
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

class BNI_MFD_WP_Table extends WP_List_Table
{
    private $order;
    private $orderby;
    private $tab;
    private $filter;
    private $search_filter;
    private $total_post;
    private $total_page;
    private $total_posts_pages;
    private $total_visible_cats;
    private $all_post_vals;
    private $cat_tracker = array();
    private $posts_per_page = 50;

    public function __construct()
    {

        parent :: __construct( array(
            'singular' => 'table example',
            'plural'   => 'table examples',
            'ajax'     => true
        ) );
        $this->set_order();
        $this->set_orderby();
        $this->set_tab();
        $this->set_filter();
        
        
    }

    private function get_processed_posts($post_type = array('post', 'page'), $per_page = 50, $paged = 0 )
    {
        $bulkToolKit_plugin = new bulkNoindexToolkit(); 

        //warm up the array variables
        $all_posts = array();
        $all_pages = array();
        $all_post_array = array();
        //set the options for getting all of the posts and pages
        $post_opts = array('numberposts' => $this->posts_per_page, 'orderby' => 'date');

        $query_args = array(
            'post_type' => $post_type,
            'posts_per_page' => $per_page,
            'post_status' => 'publish',
            'orderby' => 'publish_date',
            'order' => 'DESC',
            );

        if($paged > 1){
            
            $query_args['offset'] = ($per_page * ($paged-1));
        
        }      
        
        if(isset($_POST['s'])){
        
             $query_args['s'] = $_POST['s'];

        }
        


        $active_seo_plugin = $bulkToolKit_plugin->get_seo_plugin();

        $meta_key_noidx = $bulkToolKit_plugin->get_meta_keys('noindex');
        $meta_key_nofllw = $bulkToolKit_plugin->get_meta_keys('nofollow');        
    
        //get all posts
        $postQuery = new WP_Query($query_args);                
        $all_posts = $postQuery->posts;
        
                
            

        $pCounter = array ('post' => 0, 'page' => 0);
                
        if ( $postQuery->have_posts() ) {

            foreach($all_posts as $pDat){

                $_pDat = new StdClass;
                $_pDat->ID = $pDat->ID;
                $_pDat->post_title = $pDat->post_title;
                $_pDat->post_content = $pDat->post_content;             
                $_pDat->word_count = str_word_count(strip_tags($pDat->post_content));
                $_pDat->char_count = strlen(strip_tags($pDat->post_content));
                $_pDat->guid = $pDat->guid;
                $_pDat->post_type = $pDat->post_type;
                $_pDat->post_date = date("m/d/y",strtotime($pDat->post_date));
                $_pDat->post_date_int = strtotime($pDat->post_date);                

                if(array_key_exists($_pDat->post_type,$pCounter)){
                    $pCounter[$_pDat->post_type]++;
                }else{
                    $pCounter[$_pDat->post_type] = 1;
                }
                

                //query the post settings if AIOSEO is in use
                if($active_seo_plugin == 'aioseo'){

                    $postAISEO = aioseo()->core->db->start( 'aioseo_posts' )
                    ->where( 'post_id', $pDat->ID )
                    ->run()
                    ->model( 'AIOSEO\\Plugin\\Common\\Models\\Post' );      

                    $_pDat->noindex_status = $postAISEO->robots_noindex;
                    $_pDat->nofollow_status = $postAISEO->robots_nofollow;

                }else{
                    //If AIOSEO is not in use, use the post meta values
                    $_pDat->noindex_status = get_post_meta($pDat->ID,$meta_key_noidx,true);
                    $_pDat->nofollow_status = get_post_meta($pDat->ID,$meta_key_nofllw,true);
                
                }
                

                $all_post_array[] = $_pDat;
                
            }
        }


        //filer the page and post links if there is POST data from the search field
        if(isset($_POST['s'])){

            if(isset($_GET['pt']) && in_array($_GET['pt'],$post_type)){
            
                //find the post type in the totals, and update it
                $found_key = array_search(ucwords($_GET['pt']), array_column($this->all_post_vals, 'name'));

                $this->all_post_vals[$found_key]["total"] = array_sum($pCounter);
                
            }else{
                //adjust the number of "total pages" based on the search filter
                $this->total_posts_pages = array_sum($pCounter);
            }
            

        }


        $sort_func = '';
        if($this->orderby != '' && $this->order !=''){
            $sort_func = $this->orderby.'_'.$this->order;
        }

        //sorting hat for the table colums
        //used for the usort function

        function title_asc($a, $b) { return strcmp($a->post_title, $b->post_title);}
        function title_desc($a, $b) { return strcmp($b->post_title, $a->post_title);} 
        
        function words_asc($a, $b) { return ((int)$a->word_count > (int)$b->word_count);}
        function words_desc($a, $b) { return ((int)$a->word_count < (int)$b->word_count);}

        function characters_asc($a, $b) { return ((int)$a->char_count > (int)$b->char_count);}
        function characters_desc($a, $b) { return ((int)$b->char_count > (int)$a->char_count);}

        
        function pubdate_asc($a, $b) { return ((int)$a->post_date_int > (int)$b->post_date_int);}
        function pubdate_desc($a, $b) { return ((int)$b->post_date_int > (int)$a->post_date_int);}
        
        //only use the "usort" function if "order" is present in the query string
        if(isset($_GET['orderby']) && !in_array($_GET['orderby'],array('post_count','taxonomy'))){

            //sort the array of posts accordingly
            usort($all_post_array, $sort_func);    

        }

        return $all_post_array;
    }

    private function get_processed_cats($cat_types = array('category'), $per_page = 50, $paged = 0 )
    {
        $bulkToolKit_plugin = new bulkNoindexToolkit(); 

        //warm up the array variables
        $all_posts = array();
        $all_pages = array();
        $all_post_array = array();
        //set the options for getting all of the posts and pages
        
            
        $cat_args = array(
            'taxonomy' => $cat_types,
            'hierarchical' => True,  
            'hide_empty' => false          
        );

        
        
        if($this->orderby == 'post_count'){
            $cat_args['orderby'] = 'count';
        }elseif($this->orderby == 'taxonomy'){
            $cat_args['orderby'] = 'taxonomy';            
        }

        if($this->order == 'desc'){
            $cat_args['order'] = 'DESC';
        }elseif($this->order == 'asc'){
            $cat_args['order'] = 'ASC';
        }

        if(isset($_POST['s'])){
        
             $cat_args['search'] = $_POST['s'];

        }

        //get all categories and terms

        $all_categories = get_terms( $args = $cat_args);
        

        $count_cats = count($all_categories);            

        if($paged > 1){
            $offset = (($paged-1)*$per_page);    
        }else{
            $offset = 0;    
        }        
            
        $all_categories = array_slice($all_categories,$offset,$per_page);

        

        

        //hard code the keys for categories since there is not yet support for Yoast or AIOSEO
        $meta_key_noidx = '_bnitk_mfd_meta-robots-noindex';
        $meta_key_nofllw = '_bnitk_mfd_meta-robots-nofollow';
        
                        

        $cCounter = array ();        
        $this->total_visible_cats = 0;

        
        if ( $all_categories ) {

            foreach($all_categories as $cDat){           

                $_cDat = new StdClass;
                $_cDat->ID = $cDat->term_id;
                $_cDat->cat_title = $cDat->name;
                $_cDat->description = $cDat->description;             
                $_cDat->post_count = $cDat->count;
                $_cDat->taxonomy = $cDat->taxonomy;
                
                if(array_key_exists($_cDat->taxonomy,$cCounter)){
                    $cCounter[$_cDat->taxonomy]++;
                }else{
                    $cCounter[$_cDat->taxonomy] = 1;
                }
                

                // integration with Yoast and AIOSEO not yet supported for categories //


              
                $_cDat->noindex_status = get_term_meta($cDat->term_id,$meta_key_noidx,true);
                $_cDat->nofollow_status = get_term_meta($cDat->term_id,$meta_key_nofllw,true);
                                                    
              
                
                
                $all_post_array[] = $_cDat;
                
                if( count($all_post_array) >= $per_page ) break;
                
            }

        }
        $this->total_visible_cats = $count_cats;


        //filer the page and post links if there is POST data from the search field
        if(isset($_POST['s'])){

            if(isset($_GET['ct']) && in_array($_GET['ct'],$cat_types)){
            
                //find the post type in the totals, and update it
                $found_key = array_search(ucwords($_GET['ct']), array_column($this->all_post_vals, 'name'));

                $this->all_post_vals[$found_key]["total"] = array_sum($cCounter);
                
            }else{
                //adjust the number of "total pages" based on the search filter
               // $this->total_visible_cats = array_sum($pCounter);
            }
            

        }


        $sort_func = '';
        if($this->orderby != '' && $this->order !=''){
            $sort_func = $this->orderby.'_'.$this->order;
        }       

        return $all_post_array;
    }
    
    public function set_search_filter(){
        $search_filter = '';
        if ( isset( $_GET['s'] ) && $_GET['s'] )
            $search_filter = sanitize_text_field($_GET['s']);
        $this->search_filter = esc_sql( $search_filter );

    }

    public function set_order()
    {
        $order = 'DESC';
        if ( isset( $_GET['order'] ) && (strtolower($_GET['order']) == 'desc' || strtolower($_GET['order']) == 'asc' ))
            $order = sanitize_text_field($_GET['order']);
        $this->order = esc_sql( $order );
    }

    public function set_orderby()
    {
        $orderby = 'title';
        if ( isset( $_GET['orderby'] ) && $_GET['orderby'] )
            $orderby = sanitize_text_field($_GET['orderby']);
        $this->orderby = esc_sql( $orderby );
    }
    public function set_tab()
    {
        $tab = 'posts';

        if ( isset( $_GET['tab'] ) && (strtolower($_GET['tab'] == 'posts') || strtolower($_GET['tab'] == 'cats')) )
            $tab = sanitize_text_field($_GET['tab']);

        $this->tab = esc_sql( $tab );
    }


    /**
     * @see WP_List_Table::ajax_user_can()
     */
    public function ajax_user_can() 
    {
        return current_user_can( 'edit_posts' );
    }

    /**
     * @see WP_List_Table::no_items()
     */
    public function no_items() 
    {
        _e( 'No posts found.' );
    }

    /**
     * @see WP_List_Table::get_views()
     */
    public function get_views()
    {
        return array();
    } 


    public function set_filter()
    {
        $filter = '';
        if ( isset( $_GET['pt'] ) AND $_GET['pt'] )
            $filter = esc_sql($_GET['pt']);

        if ( isset( $_GET['ct'] ) AND $_GET['ct'] )
            $filter = esc_sql($_GET['ct']);

        $this->filter = esc_sql( $filter );
    }
 /**
     * @see WP_List_Table::get_columns_categories()
     */
    public function get_columns_categories()
    {

        $bulk_check = '<label class="screen-reader-text" for="cb-select-all">Select All</label><input id="cb-select-all" type="checkbox">';
        
        $columns = array(
            'checkbox_sel'         => __( $bulk_check ),
            'cat_title' => __( 'Title' ),
            'cat_type' => __( 'Type' ),
            'post_count' => __( 'Post Count' ),
            'noindex_tgl'  => __( 'No Index Page'  ),
            'nofollow_tgl'  => __( 'No Follow Links'  )
            
        );
        return $columns;        
    }

    /**
     * @see WP_List_Table::get_columns()
     */
    public function get_columns()
    {

        $bulk_check = '<label class="screen-reader-text" for="cb-select-all">Select All</label><input id="cb-select-all" type="checkbox">';
        
        $columns = array(
            'checkbox_sel'         => __( $bulk_check ),
            'post_title' => __( 'Title' ),
            'post_type' => __( 'Type' ),
            'word_count' => __( 'Word Count' ),
            'char_count'  => __( 'Character Count' ),
            'post_date'  => __( 'Publish Date'  ),
            'noindex_tgl'  => __( 'No Index Page'  ),
            'nofollow_tgl'  => __( 'No Follow Links'  )
            
        );
        return $columns;        
    }

    /**
     * @see WP_List_Table::get_sortable_columns()
     */
    public function get_sortable_columns()
    {
        $sortable = array(
            'post_title'         => array( 'title', true ),
            'word_count' => array( 'words', true ),
            'char_count'  => array( 'characters', true ),
            'post_date'  => array( 'pubdate', true )
        );
        return $sortable;
    }

    /**
     * @see WP_List_Table::get_sortable_columns_categories()
     */
    public function get_sortable_columns_categories()
    {
        $sortable = array(
            'cat_title' => array( 'title', true ),
            'post_count' => array( 'post_count', true ),
            'cat_type' => array( 'taxonomy', true )
            
        );
        return $sortable;
    }

    /**
     * Prepare data for display
     * @see WP_List_Table::prepare_items()
     */
    public function prepare_items_post()
    {
        
        $page_offset = 0;
        $columns  = $this->get_columns();
        $hidden   = array();
        $post_types = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( 
            $columns,
            $hidden,
            $sortable 
        );

        if(isset( $_GET['items_per_page'])){
            
            if($_GET['items_per_page'] == 'all'){
                $this->posts_per_page = -1;

            }else{
                $this->posts_per_page = $_GET['items_per_page'];
            }

        }
        
        
        if(isset( $_GET['paged'])){

            $page_offset = $_GET['paged'];
        }
        
        // Post results
        $post_type_dat = get_post_types(array('exclude_from_search'=>False,'publicly_queryable'=>True,'public'=>True));
        $post_types = array_values($post_type_dat);    
        
        //add the 'page' post type as it gets blended into the 'Post' type by default
        array_unshift($post_types, 'page');
                
        
        //tabulate the post totals
        $post_totals = array();
        foreach($post_types as $pType){
            $post_totals["total_".$pType] = wp_count_posts($type=$pType);

        }

        $this->total_posts_pages = 0;

        //tabulate all post type totals
        foreach($post_totals as $typObjName => $typObjDat){
            $this->all_post_vals[] = array(
                    'name' => ucwords(str_replace('total_','',$typObjName)),
                    'total' => $typObjDat->publish,
                    'filt_lnk' => 'pt='.strtolower(str_replace('total_','',$typObjName)));

            $this->$typObjName = $typObjDat->publish;
            $this->total_posts_pages += $typObjDat->publish;

        }

        $post_types_filt = array();
        
        if(isset( $_GET['pt']) && in_array($_GET['pt'],$post_types)){
            $post_types_filt[] = strtolower($_GET['pt']);  
        }else{
            $post_types_filt = $post_types;          
        }

    

        $postResults = $this->get_processed_posts($post_types_filt,$this->posts_per_page, $page_offset);    

        # >>>> Pagination

        if(isset( $_GET['pt']) && in_array($_GET['pt'],$post_types)){
            
           $found_key = array_search(ucwords($_GET['pt']), array_column($this->all_post_vals, 'name'));

            $total_items = $this->all_post_vals[$found_key]["total"];

        }else{
            $post_types_filt = $post_types;
            $total_items  = $this->total_posts_pages; 
        }


        $per_page     = $this->posts_per_page;
        $current_page = $this->get_pagenum();       
        

        $this->set_pagination_args( array (
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil( $total_items / $per_page )
        ) );

        // Prepare the data
        $permalink = __( 'Edit:' );
        
        $this->items = array();
        if($postResults){


            foreach ( $postResults as $key => $post )
            {

                $noindex_status = '';
                $nofollow_status = '';

                if($post->noindex_status == 1){
                    $noindex_status = 'checked';
                }

                if($post->nofollow_status == 1){
                    $nofollow_status = 'checked';
                }


                $checkbox_sel = "<input class='cb-post' id='cb-select-".$post->ID."' type='checkbox' name='post[]' value='".$post->ID."'>";

                $link     = get_edit_post_link( $post->ID );
                $no_title = __( 'No title set' );
                $title    = ! $post->post_title ? "<em>{$no_title}</em>" : $post->post_title;
                $view_aria_title = !$post->post_title ? "View Post" : "View ".$post->post_title;
                
                //initiate the object;
                $posts[ $key ] = new stdClass;

                $posts[ $key ]->post_date = $post->post_date;
                $posts[ $key ]->post_date_int = $post->post_date_int;
                $posts[ $key ]->post_title = "<a title='{$permalink} {$title}' href='{$link}'>{$title}</a>

                
    <div class='row-actions'>

    <span class='edit'><a target='_blank' href='{$link}'>Edit</a> | </span>

    <span class='view'><a target='_blank' href='".get_permalink($post->ID)."' rel='bookmark' aria-label='".$view_aria_title."'>View</a></span></div>";


                $posts[ $key ]->noindex_tgl = "<label class='bni-mfd-toggle'>
      <input type='checkbox' rel='page' class='bnitk-mfd-toggle noindex-check' name='noindex[]' value='".$post->ID."' ".$noindex_status."><i></i></label> ";

              $posts[ $key ]->nofollow_tgl = "<label class='bni-mfd-toggle'>
      <input type='checkbox' rel='page' class='bnitk-mfd-toggle nofollow-check' name='nofollow[]' value='".$post->ID."' ".$nofollow_status."><i></i></label> ";

                $posts[ $key ]->checkbox_sel = $checkbox_sel;
                
                $posts[ $key ]->word_count = number_format($post->word_count,0);
                $posts[ $key ]->char_count = number_format($post->char_count,0);

                $posts[ $key ]->post_type = ucwords($post->post_type);

            }

            $this->items = $posts;    
            
        }
        
    }

     /**
     * Prepare categories for display and bulk editing
     * @see WP_List_Table::prepare_items_cats()
     */
    public function prepare_items_cats()
    {
        
        $page_offset = 0;
        $columns  = $this->get_columns_categories();
        $hidden   = array();
        $post_types = array();
        $sortable = $this->get_sortable_columns_categories();
        $this->_column_headers = array( 
            $columns,
            $hidden,
            $sortable 
        );

        if(isset( $_GET['items_per_page'])){
            
            if($_GET['items_per_page'] == 'all'){
                $this->posts_per_page = -1;

            }else{
                $this->posts_per_page = $_GET['items_per_page'];
            }

        }
        
        
        if(isset( $_GET['paged'])){

            $page_offset = $_GET['paged'];
        }
        /*
        // Post results
        $post_type_dat = get_post_types(array('exclude_from_search'=>False,'publicly_queryable'=>True,'public'=>True));
        $post_types = array_values($post_type_dat);    
        
        //add the 'page' post type as it gets blended into the 'Post' type by default
        array_unshift($post_types, 'page');
        */
        
        //tabulate the post totals
        /*$post_totals = array();
        foreach($post_types as $pType){
            $post_totals["total_".$pType] = wp_count_posts($type=$pType);

        }*/

  
        $this->total_posts_pages = 0;

        $taxonomies = get_taxonomies($args = array('public'=>True));        
        $cat_types = array_values($taxonomies);
        
        //reset the all_post_vals array, even though it's not used for a bit
        $this->all_post_vals = array();
        

        $post_types_filt = array();
        
        if(isset( $_GET['ct']) && in_array($_GET['ct'],$cat_types)){
            $cat_types_filt[] = strtolower($_GET['ct']);  
        }else{
            $cat_types_filt = $cat_types;          
        }

    
        $postResults = $this->get_processed_cats($cat_types_filt,$this->posts_per_page, $page_offset);    
            

        $all_categories = get_terms( $args = array());
        
        foreach($all_categories as $termName){        

            if(array_key_exists($termName->taxonomy,$this->cat_tracker)){
                $this->cat_tracker[$termName->taxonomy]++;    
            }else{
                $this->cat_tracker[$termName->taxonomy] = 1;
            }
             
        }

        if($postResults && isset($_GET['ct']) && isset($_POST['s'])){

            $this->cat_tracker[$_GET['ct']] = count($postResults);

            
        }

        $this->total_posts_pages = $this->total_visible_cats;
        
                
        foreach($taxonomies as $typObjName => $typObjDat){
            if(array_key_exists($typObjName,$this->cat_tracker)){
                $total_count = $this->cat_tracker[$typObjName];    
            }else{
                $total_count = 0;
            }
            

            $this->all_post_vals[] = array(
                    'name' => ucwords(str_replace('_',' ',$typObjName)),
                    'total' => $total_count,
                    'filt_lnk' => 'ct='.strtolower(str_replace('total_','',$typObjName))
                    );

        }

        # >>>> Pagination

        if(isset( $_GET['ct']) && in_array($_GET['ct'],$post_types)){
            
           $found_key = array_search(ucwords($_GET['ct']), array_column($this->all_post_vals, 'name'));

            $total_items = $this->all_post_vals[$found_key]["total"];

        }else{            
            $total_items  = $this->total_visible_cats; 
        }


        $per_page     = $this->posts_per_page;
        $current_page = $this->get_pagenum();       
        

        $this->set_pagination_args( array (
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil( $total_items / $per_page )
        ) );

        // Prepare the data
        $permalink = __( 'Edit:' );
        if($postResults){
            foreach ( $postResults as $key => $post )
            {

                $noindex_status = '';
                $nofollow_status = '';
                

                if($post->noindex_status == 1){
                    $noindex_status = 'checked';
                }

                if($post->nofollow_status == 1){
                    $nofollow_status = 'checked';
                }


                $checkbox_sel = "<input class='cb-post' id='cb-select-".$post->ID."' type='checkbox' name='post[]' value='".$post->ID."'>";

                $link     = get_edit_term_link( $post->ID );
                
                $no_title = __( 'No title set' );
                $title    = ! $post->cat_title ? "<em>{$no_title}</em>" : $post->cat_title;
                $view_aria_title = !$post->cat_title ? "View Post" : "View ".$post->cat_title;
                
                //initiate the object;
                $posts[ $key ] = new stdClass;

                $posts[ $key ]->cat_type = $post->taxonomy;
                //$posts[ $key ]->post_date_int = $post->post_date_int;
                $posts[ $key ]->cat_title = "<a title='{$permalink} {$title}' href='{$link}'>{$title}</a>

                
    <div class='row-actions'>

    <span class='edit'><a target='_blank' href='{$link}'>Edit</a> | </span>

    <span class='view'><a target='_blank' href='".get_category_link($post->ID)."' rel='bookmark' aria-label='".$view_aria_title."'>View</a></span></div>";


                $posts[ $key ]->noindex_tgl = "<label class='bni-mfd-toggle'> <input type='checkbox' rel='cats' class='bnitk-mfd-toggle noindex-check' name='noindex[]' value='".$post->ID."' ".$noindex_status."><i></i></label> ";

              $posts[ $key ]->nofollow_tgl = "<label class='bni-mfd-toggle'>
      <input type='checkbox' rel='cats' class='bnitk-mfd-toggle nofollow-check' name='nofollow[]' value='".$post->ID."' ".$nofollow_status."><i></i></label> ";

                $posts[ $key ]->checkbox_sel = $checkbox_sel;
                $posts[ $key ]->post_count = $post->post_count;

            }

            $this->items = $posts;
        }
    }

    /**
     * A single column
     */
    public function column_default( $item, $column_name )
    {
        return $item->$column_name;
    }

    /**
     * Override of table nav to avoid breaking with bulk actions & according nonce field
     */
    public function display_tablenav( $which ) {

        
        $all_class = '';        
        $pgs_class = '';

        if(!$this->filter){
            $all_class = 'class="current" aria-current="page"';    
        }
        

        //build the current query string         
        $query_string = $this->orderby != '' ? '&orderby='.$this->orderby.'&amp;order='.$this->order : '';        
        
        if(isset($this->tab) && $this->tab == 'cats'){
            $query_string .= '&tab=cats';
        }

        ?> 
        
        <ul class="subsubsub">
            <li class="all"><a <?php echo $all_class;?> href="tools.php?page=no-index-toolkit<?php echo $query_string; ?>" >All <span class="count">(<?php echo $this->total_posts_pages;?>)</span></a> </li>

            <?php 

            foreach($this->all_post_vals as $pType){                 
                if(array_key_exists('name',$pType)){
                
                    if( strtolower($this->filter) == str_replace(' ','_',strtolower($pType["name"]))){
                        $pst_class = 'class="current" aria-current="'.strtolower($pType["name"]).'"';
                    }else{
                        $pst_class = '';
                    }                

            ?>

                | <li class="posts"><a <?php echo $pst_class; ?> href="tools.php?page=no-index-toolkit&amp;<?php echo strtolower($pType["filt_lnk"])?><?php echo $query_string; ?>"><?php echo ucwords($pType["name"]);?> <span class="count">(<?php echo $pType["total"] ; ?>)</span></a> </li> 
            <?php }  

            }
            ?>
            
        </ul>
        <div class="tablenav <?php echo esc_attr( $which ); ?>">
            
        <div class="alignleft actions">  

            <?php  $this->bulk_actions( $which ); ?>
            
        </div>
        
        <?php
        
         
        $this->extra_tablenav( $which );        
        $this->pagination( $which );
        ?>
        <br class="clear" />

        </div>
        <?php
    }
    
    public function show_items_per_page($which = ''){

        $var10 = '';
        $var20 = '';
        $var50 = '';
        $var100 = '';
        $var250 = '';
        $var500 = '';
        $var1000 = '';
        $var2500 = '';
        $var5000 = '';        
        $var10000 = '';

        if(isset($_GET['items_per_page'])){

            switch($_GET['items_per_page']){
                case "10":
                    $var10 = 'selected';
                break;
                case "20":
                    $var20 = 'selected';
                break;
                case "50":
                    $var50 = 'selected';
                break;
                case "100":
                    $var100 = 'selected';
                break;
                case "250":
                    $var250 = 'selected';
                break;
                case "500":
                    $var500 = 'selected';
                break;
                case "1000":
                    $var1000 = 'selected';
                break;
                case "2500":
                    $var2500 = 'selected';
                break;                
                case "5000":
                    $var5000 = 'selected';
                break;
                case "10000":
                    $var10000 = 'selected';
                break;               
                case "all":
                    $all_var = 'selected';
                break;
                default:
                    $var50 = 'selected';
                break;

            }
        }

        $_rendered_html  = '

            <form id="items-per-page" action="tools.php?page=no-index-toolkit" method="get">
            <input type="hidden" name="page" value="no-index-toolkit" />';
            
            if(isset($this->order)){
                $_rendered_html  .= ' <input type="hidden" name="order" value="'.$this->order.'" />'; 
            }
            
            if(isset($this->orderby)){
                $_rendered_html  .=  '<input type="hidden" name="orderby" value="'.$this->orderby.'" />';
            }

            if(isset($this->tab)){
                $_rendered_html  .=  '<input type="hidden" name="tab" value="'.$this->tab.'" />';
            }

        $_rendered_html  .= '

            <label for="items_per_page-selector-top" class="screen-reader-text">Show</label>Show
            <select name="items_per_page" id="items-per-page-selector">            
                                
                <option '.$var50.' value="50">50 Rows</option>                
                <option '.$var100.' value="100">100 Rows</option>                
                <option '.$var250.' value="250">250 Rows</option>
                <option '.$var500.' value="500">500 Rows</option>
                <option '.$var1000.' value="1000">1,000 Rows</option>
                
                
            </select>
            
            <input type="submit" id="doaction" class="button action" value="Show">

            </form>';

        echo $_rendered_html;
    }


    /**
     * Create the bulk select drop down above the table
     */
    public function bulk_actions($which = ''){

        $_rendered_html  = '

       
            <label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
            <select name="bulk_action" id="bulk-action-selector">
            <option value="-1">Bulk Actions</option>
                <option value="noindex_set" class="hide-if-no-js">Add NoIndex</option>                
                <option value="nofollow_set">Add NoFollow</option>
                <option value="noindex_unset">Remove NoIndex</option>
                <option value="nofollow_unset">Remove NoFollow</option>
            </select>
            
            <input type="submit" id="doaction" class="button action" value="Apply">
       

            
        ';

        echo $_rendered_html;
    }

    /**
     * Disables the views for 'side' context as there's not enough free space in the UI
     * Only displays them on screen/browser refresh. Else we'd have to do this via an AJAX DB update.
     * 
     * @see WP_List_Table::extra_tablenav()
     */
    public function extra_tablenav( $which )
    {
        global $wp_meta_boxes;
        $views = $this->get_views();
        if ( empty( $views ) )
            return;

        $this->views();
    }
}