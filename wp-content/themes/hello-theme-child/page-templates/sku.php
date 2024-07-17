<?php

// Template Name: Sku

get_header();

$base_url = site_url();

if($_GET['sku'] != null &&  $_GET['lang'] != null){
?>
   <script>

var sku = "<?php echo $_GET['sku']; ?>";
var lang = "<?php echo $_GET['lang']; ?>";
//alert(sku);


  
jQuery.ajax({
			url: ajax_object.url,
			method:'get',
			data: {'action': 'get_post_name','sku': sku,'lang':lang},
			dataType: 'JSON',
			success: function(res){
			  console.log("RES Post Name",res);
         var len = res.length;
			  if(len == 0){
	     		window.open("<?php echo $base_url; ?>/en/download-center","_self");
				jQuery("#notFoundModalCenter").modal('show');
			  }
			  for(var i=0; i<len;i++){
				var search_url = "<?php echo $base_url; ?>/"+res[i].prod_lang+"/product/"+res[i].prod_slug+"/?slug="+res[i].prod_slug;   
        window.open(search_url,"_self");
		    }
			}
		   });

   </script>
 <?php   
}else{
     wp_redirect("https://keys.support/en/download-center");
    exit;
}
?>
<style>
.loader {
  border: 16px solid #f3f3f3;
  border-radius: 50%;
  border-top: 16px solid #3498db;
  width: 120px;
  height: 120px;
  -webkit-animation: spin 2s linear infinite; /* Safari */
  animation: spin 2s linear infinite;
  margin: 150px 0 0 50%;

}

/* Safari */
@-webkit-keyframes spin {
  0% { -webkit-transform: rotate(0deg); }
  100% { -webkit-transform: rotate(360deg); }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.loader_container{
  height:400px;
}
</style>
<div class="container loader_container">
<div class="loader"></div>
</div>
  <!-- Modal -->
  <div class="modal fade" id="notFoundModalCenter" tabindex="-1" role="dialog" aria-labelledby="notFoundModalCenter" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="notFoundModalCenter">Product SKU</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Not Found
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<?php get_footer(); ?>