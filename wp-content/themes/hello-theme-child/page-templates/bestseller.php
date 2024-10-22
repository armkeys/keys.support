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
$bestseller = [];
$bestseller[] = array(
    'product_name' => "Office 2021 Standard",
    'price' => ["39.90 €", "Fr.  40.20"],
    'image' => $bestseller_images . "1411.png",
    'url' => "https://keys.express/{$store_lang_url}/office-2021-standard.html"
);
$bestseller[] = array(
    'product_name' => "Office 2021 Professional Plus",
    'price' => ["44.90 €", "Fr.  45.25"],
    'image' => $bestseller_images . "1421.png",
    'url' => "https://keys.express/{$store_lang_url}/office-2021-professional-plus.html",
);
$bestseller[] = array(
    'product_name' => "Windows 10 Professional",
    'price' => ["19.90 €", "Fr.  20,05"],
    'image' => $bestseller_images . "2210.png",
    'url' => "https://keys.express/{$store_lang_url}/windows-10-professional-1.html",
);
$bestseller[] = array(
    'product_name' => "Windows 11 Professional",
    'price' => ["29.90 €", "Fr.  30,15"],
    'image' => $bestseller_images . "2310.png",
    'url' => "https://keys.express/{$store_lang_url}/windows-11-professional.html",
);
$bestseller[] = array(
    'product_name' => "Office 2019 Standard",
    'price' => ["29.90 €", "Fr.  30,15"] ,
    'image' => $bestseller_images . "1311.png",
    'url' => "https://keys.express/{$store_lang_url}/office-2019-standard.html",
);
$bestseller[] = array(
    'product_name' => "Office 2019 Professional Plus",
    'price' => ["32.90 €","Fr.  33,20"],
    'image' => $bestseller_images . "1321.png",
    'url' => "https://keys.express/{$store_lang_url}/office-2019-professional-plus.html",
);
$bestseller[] = array(
    'product_name' => "Kaspersky Standard 1D/1Y",
    'price' => ["14.30 €","Fr.  14,45"],
    'image' => $bestseller_images . "5409.png",
    'url' => "https://keys.express/{$store_lang_url}/kaspersky-plus-1d-1y-4.html",
);

$bestseller[] = array(
    'product_name' => "Windows 11 Pro Retail",
    'price' => ["34.90 €","Fr.  35,20"],
    'image' => $bestseller_images . "2309.png",
    'url' => "https://keys.express/{$store_lang_url}/windows-11-pro-retail.html",
);

$bestseller[] = array(
    'product_name' => "Windows 11 Pro Upgrade",
    'price' => ["39.90 €", "Fr.  40,20"],
    'image' => $bestseller_images . "2316.png",
    'url' => "https://keys.express/{$store_lang_url}/windows-11-pro-upgrade.html",
);

$bestseller[] = array(
    'product_name' => "Office 2024 Professional Plus",
    'price' => ["24.95 €", "Fr.  23,35"],
    'image' => $bestseller_images . "1521.png",
    'url' => "https://keys.express/{$store_lang_url}/office-2024-standard-4.html",
);

$bestseller[] = array(
    'product_name' => "Office 2021 Standard MacOS",
    'price' => ["79.90 €", "Fr.  80,55"],
    'image' => $bestseller_images . "1959.png",
    'url' => "https://keys.express/{$store_lang_url}/office-2021-standard-macos.html",
);

$bestseller[] = array(
    'product_name' => "Server 2022 Standard 16-core (1 User)",
    'price' => ["649.00 €", "Fr.  654,20"],
    'image' => $bestseller_images . "3084.png",
    'url' => "https://keys.express/{$store_lang_url}/server-2022-standard-16-core-1-user.html",
);

?>

<div id="best_seller">
<h3><?php echo $title; ?></h3>
<div class="row">
    <?php foreach ($bestseller as $k=>$data): ?>
    <div class="column">
    <a href="<?php echo $data['url']; ?>"><img src="<?php echo $data['image']; ?>" /></a>
        <p><?php echo $data['product_name']; ?></p>
        <p class="price"><?php echo ($currency=="eur") ? $data['price'][0] : $data['price'][1] ; ?></p>
        <a href="<?php echo $data['url']; ?>"><button type="button" class="btn btn-danger btn-block"><?php echo $btn_text; ?></button></a>
    </div>
    <?php endforeach; ?>
</div>

</div>