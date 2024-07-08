<?php

// Template Name: Redirection

get_header();

if(get_current_user_id()){

    $user_meta = get_userdata(get_current_user_id());
    $all_user_meta = get_user_meta(get_current_user_id());
    $user_roles = $user_meta->roles;
    $it_personnel = $all_user_meta['it_personnel'][0];

    //var_dump(in_array("customer_support",$user_roles));

    if (in_array("marketing",$user_roles) && $it_personnel == 'No')
    {
      $redirect_url = home_url()."/marketing";
      wp_redirect($redirect_url);
      exit;
    }elseif(in_array("human_resource",$user_roles) && $it_personnel == 'No'){
      $redirect_url = home_url()."/human-resource";
      wp_redirect($redirect_url);
      exit;
    }elseif(in_array("customer_support",$user_roles) && $it_personnel == 'No'){
      $redirect_url = home_url();
      wp_redirect($redirect_url);
      exit;
    }elseif(in_array("it",$user_roles) && $it_personnel == 'No'){
      $redirect_url = home_url()."/information-technology";
      wp_redirect($redirect_url);
      exit;
    }elseif(in_array("administrator",$user_roles) && $it_personnel == 'Yes'){
      $redirect_url = home_url()."/wp-admin/profile.php";
      wp_redirect($redirect_url);
      exit;
    }else{
      wp_redirect(home_url());
      exit;
    }


}else{
  wp_redirect(home_url());
 exit;
}


?>
<?php get_footer(); ?>