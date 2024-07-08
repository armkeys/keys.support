<?php

function kb_single_post_meta_data ($atts){
	ob_start();
	$lang = apply_filters( 'wpml_current_language', NULL );
    $id = get_the_ID();
    global $wpdb;

    /*WLM*/
    /*$results = $wpdb->get_results( 
        $wpdb->prepare("SELECT level_id FROM sup_wlm_contentlevels WHERE type='docs' AND level_id <> 'Protection' AND content_id=%d",$id) 
     );
     $option_value = get_option('_transient_wlm3_valid_level_names');*/ 

     $results = $wpdb->get_results( 
      $wpdb->prepare("SELECT meta_value FROM sup_postmeta WHERE meta_key='_members_access_role' AND post_id=%d",$id) 
   );
  
    ?>	                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       

      <ul class="nav" >
        <li class="nav-item border-right">
         <a class="nav-link active" href="#"><span style="color: #4d4d4d !important;">Tag: <?php     
              $results_tag = $wpdb->get_results( 
                $wpdb->prepare("SELECT * FROM sup_term_relationships as stp INNER JOIN sup_term_taxonomy as stt ON stp.term_taxonomy_id = stt.term_taxonomy_id INNER JOIN sup_terms as st ON stt.term_id = st.term_id WHERE  stt.taxonomy='doc_tag' AND stp.object_id =%d",$id) 
             );  
            
             if(count($results_tag) > 0){
                for($h=0; $h < count($results_tag); $h++){
                  echo ucwords($results_tag[$h]->name) . ((count($results_tag) - 1) != $h ? ', ' : ''); 
                }
             }else{
                echo "None";
             }
             
            
         ?></span></a>
        </li>
        <li class="nav-item border-right">
        <a class="nav-link" href="#"><span style="color: #4d4d4d !important;">Access: 
        <?php 
         /*WLM*/
         /*if(count($results) > 0){
            for ($i = 0; $i < count($results); $i++) {
            //echo $option_value[$results[$i]->level_id];
         
            if($option_value[$results[$i]->level_id] != ''){
            echo $option_value[$results[$i]->level_id] . ((count($results) - 1) != $i ? ',' : ''); 
            }
            }
          }*/
          //var_dump(count($results));
          if(count($results) > 0){
            for ($i = 0; $i < count($results); $i++) {
                 echo ucwords($results[$i]->meta_value) . ((count($results) - 1) != $i ? ', ' : ''); 
            }
          }else{
            echo 'Public';
          }


        ?>

        </span></a>        
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#"><span style="color: #4d4d4d !important;">Updated: <?php echo get_the_modified_date(); ?></span></a>     
        </li>
      </ul>
  
 <?php
 return ob_get_clean(); 
	
}
add_shortcode('single_post_meta_data', 'kb_single_post_meta_data');