<?php
// $eur_currency = [ EN, FR, ES, IT, PT, FR_BE DE ]
// $chf_currency = ['IT_CH', 'DE_CH', 'FR_CH'];

switch ($lang) {
    case 'en':
        $store_lang_url = "EN";
        $title = "Please checkout our bestseller";
        $btn_text = "Buy Now";
        $currency = "eur";
        break;
    case 'de':
        $store_lang_url = "DE";
        $title = "Bitte schauen Sie sich unsere Bestseller an";
        $btn_text = "Jetzt kaufen";
        $currency = "eur";
        break;
    case 'be':
        $store_lang_url = "FR_BE";
        $title = "Bekijk onze bestseller";
        $btn_text = "Koop nu";
        $currency = "eur";
        break;
    case 'fr':
        $store_lang_url = "FR";
        $title = "Veuillez consulter notre best-seller";
        $btn_text = "Acheter maintenant";
        $currency = "eur";
        break;
    case 'it':
        $store_lang_url = "IT";
        $title = "Per favore, dai un'occhiata al nostro best seller.";
        $btn_text = "Acquista ora";
        $currency = "eur";
        break;
    case 'pt-pt':
        $store_lang_url = "PT";
        $title = "Por favor, confira o nosso best-seller";
        $currency = "eur";
        break;
    case 'es':
        $store_lang_url = "ES";
        $title = "Por favor, echa un vistazo a nuestro best-seller";
        $btn_text = "Comprar ahora";
        $currency = "eur";
        break;
    case 'cs':
    case 'el':
    case 'sk':
        $store_lang_url = "EN";
        $title = "Please checkout our bestseller";
        $btn_text = "Buy Now";
        $currency = "eur";
        break;
    default:
        $store_lang_url = "EN";
        $title = "Please checkout our bestseller";
        $btn_text = "Buy Now";
        $currency = "eur";
        break;
}

$bestseller_images = get_stylesheet_directory_uri() . "/page-templates/bestseller/images/";
$bestseller = getBestSeller();

?>

<div id="best_seller">
<h3><?php echo $title; ?></h3>
<div class="row">
    <?php foreach ($bestseller as $k=>$data): ?>
    <div class="column">
    <a href="<?php echo $data['url']; ?>"><img src="<?php echo $data['image']; ?>" /></a>
        <p style="font-size: 10px;text-align: center;font-weight: bold;"><?php echo $data['product_name']; ?></p>
        <p class="price"><?php echo $data['price'][0] ; ?></p>
        <!--<a href="<?php echo $data['url']; ?>"><button type="button" class="btn btn-danger btn-block"><?php echo $btn_text; ?></button></a>-->
    </div>
    <?php endforeach; ?>
</div>

</div>