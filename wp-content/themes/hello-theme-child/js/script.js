

jQuery(document).ready(function ($){
	
	let baseUrl = helloScriptVars.baseUrl;

	/*Download center*/
	var ur = window.location.href;
	var downloadKeywords = ['/en/download-center/', '/download-centrum/', '/centre-de-telechargement/', '/κέντρο-λήψης/', '/centro-download/', '/centro-de-download/', '/centro-de-descargas/', '/centrum-stahovani/', '/indirme-merkezi/', '/downloadcentrum/'];

	if (downloadKeywords.some(keyword => ur.includes(keyword))) {
		jQuery('#software-filter-download').select2({
			width: '100%'
		});
	}

	var system = jQuery("select.system-filter-download").val();

	// Hide all elements by default
	// jQuery(".system-64-bit-label, .system-64-bit-button, .system-32-bit-label, .system-32-bit-button").hide();

	// Show the relevant elements based on the selected system
	// if (system === "64-bit") {
	// 	jQuery(".system-64-bit-label, .system-64-bit-button").show();
	// } else if (system === "32-bit") {
	// 	jQuery(".system-32-bit-label, .system-32-bit-button").show();
	// } else {
	// 	// Default case
	// 	jQuery(".system-64-bit-label, .system-64-bit-button").show();
	// }


	jQuery('#category-filter-download').on('change', function () {
		var ur = window.location.href;
		var product_term_id = jQuery(this).val();
		console.log('PRODUCT TERM ID', product_term_id);
	
		jQuery("#software-filter-download").html("");
	
		var languageMap = {
			'en': 'Software',
			'de': 'Programme',
			'nl': 'Software',
			'fr': 'Logiciel',
			'el': 'Λογισμικό',
			'it': 'Software',
			'pt-pt': 'Programas',
			'es': 'Software',
			'cs': 'Software',
			'tr': 'Yazılım',
			'sk': 'Software',
			'be': 'Software'
		};
	
		var languageCode = Object.keys(languageMap).find(code => ur.includes('/' + code + '/'));
	
		if (!languageCode) {
			languageCode = 'en'; // Default to English if no matching language is found
		}
	
		var software = languageMap[languageCode];
	
		jQuery("#software-filter-download").append("<option value=''>" + software + "</option>");
	
		jQuery.ajax({
			url: ajax_object.url,
			method: 'get',
			data: { 'action': 'get_software', 'product_term_id': product_term_id },
			dataType: 'JSON',
			success: function (res) {
				console.log("RES CATEGORY", res);
				var len = res.length;
				console.log("RES LENGTH", len);
	
				if (len > 0) {
					for (var i = 0; i < len; i++) {
						jQuery("#software-filter-download").append("<option value=\"" + res[i].prod_slug + "\">" + res[i].prod_post_title + "</option>");
					}
				} else {
					var url = window.location.toString();
					var langUrl = "https://keys.support/" + languageCode + "/download-center";
					window.open(langUrl + '?return=false', "_self");
				}
			}
		});
	});
	
	jQuery('#software-filter-download').on('change', function () {
		let slug = jQuery(this).val();
		let languageCodes = ['en', 'de', 'nl', 'fr', 'el', 'it', 'pt-pt', 'es', 'cs', 'tr', 'sk', 'be'];
	
		let url = window.location.toString();
		let langUrl;
	
		//langUrl = baseUrl + "/product/" + slug + "/?slug=" + slug;
		//window.open(langUrl, "_self", 'slug=' + slug);

		 for (let i = 0; i < languageCodes.length; i++) {
		 	let langCode = languageCodes[i];
		 	if (url.includes('/' + langCode + '/')) {
		 		langUrl = baseUrl +"/"+ langCode + "/product/" + slug + "/?slug=" + slug;
		 		window.open(langUrl, "_self", 'slug=' + slug);
		 		break;
		 	}
		 }
	});

	jQuery('select.system-filter-download').on('change', function(){
		var selectedSystem = jQuery(this).val();
		
		// Hide all elements by default
		jQuery(".system-64-bit-label, .system-64-bit-button, .descr_64, .system-32-bit-label, .system-32-bit-button, .descr_32").hide();
	
		// Show the relevant elements based on the selected system
		if (selectedSystem === "64-bit") {
			jQuery(".system-64-bit-label, .system-64-bit-button, .descr_64").show();
		} else if (selectedSystem === "32-bit") {
			jQuery(".system-32-bit-label, .descr_32").show();
			jQuery(".system-32-bit-button").css("display", "inline-block");
		} else {
			// Default case
			jQuery(".system-64-bit-label, .system-64-bit-button, .descr_64").show();
		}
	});
	
	jQuery("#search_by_sku_submit").click(function () {
		let sku = jQuery("#search_by_sku_input").val();
		let url = window.location.toString();
		let lang = "en"; // Default to English
	
		// Determine language dynamically
		for (let language of ["en", "de", "nl", "fr", "el", "it", "pt-pt", "es", "cs", "tr", "sk", "be"]) {
			if (url.includes("/" + language + "/")) {
				lang = language;
				break;
			}
	}
	
	jQuery.ajax({
			url: ajax_object.url,
			method: 'get',
			data: { 'action': 'get_post_name', 'sku': sku, 'lang': lang },
			dataType: 'JSON',
			success: function (res) {
				console.log("RES Post Name", res);
				var len = res.length;
				if (len > 0) {
					for (var i = 0; i < len; i++) {
						var search_url = "https://keys.support/" + res[i].prod_lang + "/product/" + res[i].prod_slug + "/?slug=" + res[i].prod_slug;
						window.open(search_url, "_self");
					}
				} else {
					jQuery("#notFoundModalCenter").modal('show');
				}
			}
		});
	});
	
	 /*Service Center*/ 
	 const translations = {
		en: {
			email: "EMAIL",
			company: "COMPANY (OPTIONAL)",
			surname: "SURNAME (OPTIONAL)",
			firstname: "FIRST NAME (OPTIONAL)",
			orderNumber: "ORDER NUMBER",
			productKey: "PRODUCT KEY/ SERIALIZER FOR EXCHANGE",
			uploadErrorMsg: "UPLOAD ERROR MESSAGE (PDF, PNG OR JPG - MAX. 2MB)",
			additionalService: "ADDITIONAL PAID SERVICE",
			errorMessage: "ERROR MESSAGE",
			emailPlaceholder: "Email Address",
			companyPlaceholder: "Company",
			orderNumberPlaceholder: "Order number",
			exchangeOrderNumberPlaceholder: "Order number",
			exchangeOrderNumberLabel: "ORDER NUMBER",
			exchangeProductKeyLabel: "PRODUCT KEY",
			productNameExchange: "PRODUCT NAME FOR EXCHANGE",
			uploadInvoice: "UPLOAD INVOICE / PROOF OF PURCHASE (PDF, PNG, JPG - MAX 2MB)",
			noJobListing: "No jobs found"
		},
		de: {
			email: "EMAIL",
			company: "COMPANY (OPTIONAL)",
			surname: "SURNAME (OPTIONAL)",
			firstname: "FIRST NAME (OPTIONAL)",
			orderNumber: "ORDER NUMBER",
			productKey: "PRODUCT KEY/ SERIALIZER FOR EXCHANGE",
			uploadErrorMsg: "UPLOAD ERROR MESSAGE (PDF, PNG OR JPG - MAX. 2MB)",
			additionalService: "ADDITIONAL PAID SERVICE",
			errorMessage: "FEHLERMELDUNG",
			emailPlaceholder: "E-Mail-Addresse",
			companyPlaceholder: "Gesellschaft",
			orderNumberPlaceholder: "Bestellnummer",
			exchangeOrderNumberPlaceholder: "Bestellnummer",
			exchangeOrderNumberLabel: "ORDER NUMBER",
			exchangeProductKeyLabel: "PRODUKTSCHLÜSSEL",
			productNameExchange: "PRODUKTNAME ZUM AUSTAUSCH",
			uploadInvoice: "RECHNUNG / KAUFNACHWEIS HOCHLADEN (PDF, PNG, JPG - MAX 2MB)",
			noJobListing: "Keine Jobs gefunden"
		},
		el: {
			email: "E-MAIL*",
			company: "ΕΤΑΙΡΕΙΑ (ΠΡΟΑΙΡΕΤΙΚΟ)",
			surname: "ΕΠΩΝΥΜΟ (ΠΡΟΑΙΡΕΤΙΚΟ)",
			firstname: "ΟΝΟΜΑ (ΠΡΟΑΙΡΕΤΙΚΟ)",
			orderNumber: "ΑΡΙΘΜΟΣ ΠΑΡΑΓΓΕΛΙΑΣ*",
			productKey: "ΚΛΕΙΔΙ ΠΡΟΪΟΝΤΟΣ / SERIALIZER ΓΙΑ ΑΝΤΑΛΛΑΓΗ",
			uploadErrorMsg: "ΑΠΟΣΤΟΛΗ ΜΗΝΥΜΑΤΟΣ ΣΦΑΛΜΑΤΟΣ (PDF, PNG Ή JPG - MAX. 2MB)",
			additionalService: "ΠΡΟΣΘΕΤΗ ΠΑΡΟΧΗ ΥΠΗΡΕΣΙΩΝ (ΜΕ ΕΠΙΒΑΡΥΝΣΗ ΚΟΣΤΟΥΣ)",
			errorMessage: "ΜΗΝΥΜΑ ΣΦΑΛΜΑΤΟΣ",
			emailPlaceholder: "Διεύθυνση ηλεκτρονικού ταχυδρομείου",
			companyPlaceholder: "Εταιρία",
			orderNumberPlaceholder: "Αριθμός παραγγελίας",
			exchangeOrderNumberPlaceholder: "Αριθμός παραγγελίας",
			exchangeOrderNumberLabel: "ΑΡΙΘΜΟΣ ΠΑΡΑΓΓΕΛΙΑΣ*",
			exchangeProductKeyLabel: "ΚΛΕΙΔΙ ΠΡΟΪΟΝΤΟΣ",
			productNameExchange: "ΟΝΟΜΑ ΠΡΟΪΟΝΤΟΣ ΓΙΑ ΑΝΤΑΛΛΑΓΗ",
			uploadInvoice: "ΑΝΕΒΑΣΤΕ ΤΙΜΟΛΟΓΙΟ / ΑΠΟΔΕΙΞΗ ΑΓΟΡΑΣ (PDF, PNG, JPG - MAX 2MB)",
			noJobListing: "Δεν βρέθηκαν θέσεις εργασίας"
		},
		fr: {
			email: "E-MAIL*",
			company: "SOCIÉTÉ (OPTIONNEL)",
			surname: "SURNAME (OPTIONAL)",
			firstname: "PRÉNOM (OPTIONNEL)",
			orderNumber: "NUMÉRO DE COMMANDE*",
			productKey: "CLÉ DE PRODUIT / SERIALIZER POUR L’ÉCHANGE",
			uploadErrorMsg: "MESSAGE D'ERREUR DE CHARGEMENT (PDF, PNG OU JPG - MAX. 2MB)",
			additionalService: "SERVICE PAYABLE SUPPLEMENTAIRE",
			errorMessage: "MESSAGE D'ERREUR",
			emailPlaceholder: "Adresse e-mail",
			companyPlaceholder: "Compagnie",
			orderNumberPlaceholder: "Numéro de commande",
			exchangeOrderNumberPlaceholder: "Numéro de commande",
			exchangeOrderNumberLabel: "NUMÉRO DE COMMANDE*",
			exchangeProductKeyLabel: "CLÉ DE PRODUIT",
			productNameExchange: "NOM DU PRODUIT POUR L'ÉCHANGE",
			uploadInvoice: "TÉLÉCHARGER FACTURE / PREUVE D'ACHAT (PDF, PNG, JPG - MAX 2MB)",
			noJobListing: "Aucun emploi trouvé"
		},
		it: {
			email: "E-MAIL",
			company: "SOCIETÀ (OPZIONALE)",
			surname: "COGNOME (OPZIONALE)",
			firstname: "NOME (OPZIONALE)",
			orderNumber: "NUMERO D'ORDINE*",
			productKey: "CHIAVE PRODOTTO / SERIALIZER",
			uploadErrorMsg: "CARICA MESSAGGIO D'ERRORE (PDF,PNG O JPG - MAX. 2MB)",
			additionalService: "SERVIZZI AGGIUNTIVI A PAGAMENTO",
			errorMessage: "MESSAGGIO DI ERRORE",
			emailPlaceholder: "Indirizzo e-mail",
			companyPlaceholder: "Azienda",
			orderNumberPlaceholder: "Numero d'ordine",
			exchangeOrderNumberPlaceholder: "Numero d'ordine",
			exchangeOrderNumberLabel: "NUMERO D'ORDINE*",
			exchangeProductKeyLabel: "CHIAVE PRODOTTO",
			productNameExchange: "NOME PRODOTTO PER CAMBIO",
			uploadInvoice: "CARICA FATTURA / PROVA DI ACQUISTO (PDF, PNG, JPG - MAX 2MB)",
			noJobListing: "Nessun lavoro trovato"
		},
		pt: {
			email: "EMAIL",
			company: "COMPANHIA (OPCIONAL)",
			surname: "SOBRENOME (OPCIONAL)",
			firstname: "NOME (OPCIONAL)",
			orderNumber: "NÚMERO DO PEDIDO*",
			productKey: "CHAVE DO PRODUTO / SERIALIZER PARA TROCA",
			uploadErrorMsg: "CARREGAR MENSAGEM DE ERRO (PDF, PNG ODER JPG - MAX. 2MB)*",
			additionalService: "SERVIÇO PAGO ADICIONAL",
			errorMessage: "MENSAGEM DE ERRO",
			emailPlaceholder: "Endereço de e-mail",
			companyPlaceholder: "Companhia",
			orderNumberPlaceholder: "Número do pedido",
			exchangeOrderNumberPlaceholder: "Número do pedido",
			exchangeOrderNumberLabel: "NÚMERO DO PEDIDO*",
			exchangeProductKeyLabel: "CHAVE DO PRODUTO",
			productNameExchange: "NOME DO PRODUTO PARA TROCA",
			uploadInvoice: "CARREGAR FATURA/PROVA DE COMPRA (PDF, PNG, JPG - MAX 2MB)",
			noJobListing: "Nenhum emprego encontrado"
		},
		es: {
			email: "CORREO ELECTRÓNICO*",
			company: "EMPRESA (OPCIONAL)",
			surname: "APELLIDO (OPCIONAL)",
			firstname: "NOMBRE (OPCIONAL)",
			orderNumber: "NÚMERO DE ORDEN*",
			productKey: "CLAVE DE PRODUCTO",
			uploadErrorMsg: "MENSAJE DE ERROR DE CARGA (PDF, PNG O JPG - MAX. 2MB)*",
			additionalService: "SERVICIO ADICIONAL PAGADO",
			errorMessage: "MENSAJE DE ERROR",
			emailPlaceholder: "Dirección de correo electrónico",
			companyPlaceholder: "Compañía",
			orderNumberPlaceholder: "Número de orden",
			exchangeOrderNumberPlaceholder: "Número de orden",
			exchangeOrderNumberLabel: "NÚMERO DE ORDEN*",
			exchangeProductKeyLabel: "CLAVE DE PRODUCTO",
			productNameExchange: "NOMBRE DEL PRODUCTO PARA INTERCAMBIO",
			uploadInvoice: "CARGAR FACTURA / PRUEBA DE COMPRA (PDF, PNG, JPG - MAX 2MB)",
			noJobListing: "No se encontraron trabajos"
		},
		nl: {
			email: "E-MAIL",
			company: "BEDRIJF (OPTIONEEL)",
			surname: "ACHTERNAAM (OPTIONEEL)",
			firstname: "VOORNAAM (OPTIONEEL)",
			orderNumber: "BESTELNUMMER",
			productKey: "PRODUCTSLEUTEL/ SERIALISATOR VOOR UITWISSELING",
			uploadErrorMsg: "UPLOAD FOUTBERICHT (PDF, PNG OF JPG - MAX. 2MB)",
			additionalService: "EXTRA BETAALDE SERVICE",
			errorMessage: "FOUTMELDING",
			emailPlaceholder: "E-mailadres",
			companyPlaceholder: "Bedrijf",
			orderNumberPlaceholder: "Bestellingsnummer",
			exchangeOrderNumberPlaceholder: "Bestellingsnummer",
			exchangeOrderNumberLabel: "BESTELNUMMER",
			exchangeProductKeyLabel: "PRODUCTSLEUTEL",
			productNameExchange: "PRODUCTNAAM VOOR RUIL",
			uploadInvoice: "FACTUUR UPLOADEN / AANKOOPBEWIJS (PDF, PNG, JPG - MAX 2MB)",
			noJobListing: "Geen banen gevonden"
		},
		cs: {
			email: "E-MAIL",
			company: "SPOLEČNOST (VOLITELNÉ)",
			surname: "PŘÍJMENÍ (VOLITELNÉ)",
			firstname: "KŘESTNÍ JMÉNO (VOLITELNÉ)",
			orderNumber: "ČÍSLO OBJEDNÁVKY",
			productKey: "PRODUKTOVÝ KLÍČ/SERIALIZÁTOR PRO VÝMĚNU",
			uploadErrorMsg: "CHYBOVÁ ZPRÁVA NAHRÁNÍ (PDF, PNG OR JPG - MAX. 2MB)*",
			additionalService: "DODATEČNÁ PLACENÁ SLUŽBA",
			errorMessage: "CHYBOVÉ HLÁŠENÍ",
			emailPlaceholder: "Emailová adresa",
			companyPlaceholder: "Společnost",
			orderNumberPlaceholder: "Číslo objednávky",
			exchangeOrderNumberPlaceholder: "Číslo objednávky",
			exchangeOrderNumberLabel: "ČÍSLO OBJEDNÁVKY",
			exchangeProductKeyLabel: "PRODUKTOVÝ KLÍČ",
			productNameExchange: "NÁZEV PRODUKTU PRO VÝMĚNU",
			uploadInvoice: "NAHRAJTE FAKTURU / DOKLAD O NÁKUPU (PDF, PNG OR JPG - MAX 2MB)",
			noJobListing: "Nenašli sa žiadne úlohy"
		},
		sk: {
			email: "E-MAILOVÁ ADRESA",
			company: "SPOLOČNOSŤ (VOLITEĽNÉ)",
			surname: "PRIEZVISKO (VOLITEĽNÉ)",
			firstname: "KRSTNÉ MENO (VOLITEĽNÉ)",
			orderNumber: "ČÍSLO OBJEDNÁVKY",
			productKey: "PRODUKTOVÝ KĽÚČ/SERIALIZÁTOR PRE VÝMENU",
			uploadErrorMsg: "NAHRAŤ CHYBOVÚ HLÁŠKU (PDF, PNG ALEBO JPG - MAX. 2MB)",
			additionalService: "DODATOČNÁ PLACENÁ SLUŽBA",
			errorMessage: "CHYBOVÉ HLÁSENIE",
			emailPlaceholder: "Emailová adresa",
			companyPlaceholder: "Spoločnosť",
			orderNumberPlaceholder: "Číslo objednávky",
			exchangeOrderNumberPlaceholder: "Číslo objednávky",
			exchangeOrderNumberLabel: "ČÍSLO OBJEDNÁVKY",
			exchangeProductKeyLabel: "PRODUKTOVÝ KĽÚČ",
			productNameExchange: "NÁZOV PRODUKTU PRE VÝMENU",
			uploadInvoice: "NAHRAJTE FAKTURU / DOKLAD O NÁKUPE (PDF, PNG ALEBO JPG - MAX 2MB)",
			noJobListing: "Nenašli sa žiadne úlohy"
		},
		be: {
			email: "E-MAILADRES",
			company: "BEDRIJF (OPTIONEEL)",
			surname: "ACHTERNAAM (OPTIONEEL)",
			firstname: "VOORNAAM (OPTIONEEL)",
			orderNumber: "BESTELLINGNUMMER",
			productKey: "PRODUCTCODE / SERIENUMMER VOOR UITWISSELING",
			uploadErrorMsg: "FOUTMELDING UPLOADEN (PDF, PNG OF JPG - MAX. 2MB)",
			additionalService: "EXTRA BETAALDE SERVICE",
			errorMessage: "FOUTMELDING",
			emailPlaceholder: "E-mailadres",
			companyPlaceholder: "Bedrijf",
			orderNumberPlaceholder: "Bestellingnummer",
			exchangeOrderNumberPlaceholder: "Bestellingnummer",
			exchangeOrderNumberLabel: "BESTELLINGNUMMER",
			exchangeProductKeyLabel: "PRODUCTCODE",
			productNameExchange: "PRODUCTNAAM VOOR RUIL",
			uploadInvoice: "FACTUUR UPLOADEN / AANKOOPBEWIJS (PDF, PNG OF JPG - MAX 2MB)",
			noJobListing: "Geen banen gevonden"
		},
		tr: {
			email: "E-POSTA",
			company: "ŞİRKET (İSTEĞE BAĞLI)",
			surname: "SOYAD (İSTEĞE BAĞLI)",
			firstname: "ADI (İSTEĞE BAĞLI)",
			orderNumber: "SİPARİŞ NUMARASI",
			productKey: "ÜRÜN ANAHTARI/ DEĞİŞİM İÇİN SERİSİ",
			uploadErrorMsg: "YÜKLE HATA MESAJI (PDF, PNG OR JPG - MAX. 2MB)*",
			additionalService: "EK ÜCRETLİ HİZMET",
			errorMessage: "HATA MESAJI",
			emailPlaceholder: "E-posta Adres",
			companyPlaceholder: "Şirket",
			orderNumberPlaceholder: "Sipariş numarası",
			exchangeOrderNumberPlaceholder: "Sipariş numarası",
			exchangeOrderNumberLabel: "SİPARİŞ NUMARASI",
			exchangeProductKeyLabel: "ÜRÜN ANAHTARI",
			productNameExchange: "DEĞİŞİM ÜRÜN ADI",
			uploadInvoice: "FATURA / SATIN ALMA BELGESİNİ YÜKLE (PDF, PNG, JPG - MAX 2MB)",
			noJobListing: "Hiçbir iş bulunamadı"
		}
	};

	const ur_sc = window.location.href;
	let lang = ur_sc.match(/\/(en|de|el|fr|it|pt-pt|es|tr|cs|nl|sk|be)\//);

	if (lang) {
		lang = lang[1];
		const t = translations[lang];

		$(".email_input_wrapper label").html(t.email);
		$(".company_input_wrapper label").html(t.company);
		$(".surname_input_wrapper label").html(t.surname);
		$(".firstname_input_wrapper label").html(t.firstname);
		$(".order_number_input_wrapper label").html(t.orderNumber);
		$(".product_key_input_wrapper label").html(t.productKey);
		$(".upload_error_msg .ff-el-input--label label").html(t.uploadErrorMsg);
		$(".additional_payable_service .ff-el-input--label label").html(t.additionalService);
		$(".error_message .ff-el-input--label label").html(t.errorMessage);
		$(".email_input").attr('placeholder', t.emailPlaceholder);
		$(".company_input").attr('placeholder', t.companyPlaceholder);
		$(".order_number_input").attr('placeholder', t.orderNumberPlaceholder);
		$(".order_number_exchange_input_wrapper .ff-el-input--content table.ff_repeater_table thead tr th input").attr('placeholder', t.exchangeOrderNumberPlaceholder);
		$(".order_number_exchange_input_wrapper .ff-el-input--content table.ff_repeater_table thead tr th label").html(t.exchangeOrderNumberLabel);
		$(".product_key_exchange_input_wrapper .ff-el-input--content table.ff_repeater_table thead tr th label").html(t.exchangeProductKeyLabel);
		$(".product_name_exchange .ff-el-input--label label").html(t.productNameExchange);
		$(".upload_invoice .ff-el-input--label label").html(t.uploadInvoice);

		if (t.noJobListing) {
			$(".no-job-listing .no-job-listing-text").html(t.noJobListing);
		}
	}

	jQuery('#fluentform_3 .ff-btn-submit').click(function() {
		var email = jQuery( "#ff_3_email" ).val();
		var company = jQuery( ".company_input" ).val();
		var firstname = jQuery( ".firstname_input" ).val();
		var surname = jQuery( ".surname_input" ).val();
		var order_number = jQuery( ".order_number_input" ).val();
		var product_key = jQuery( ".product_key_input" ).val();
		//var file = jQuery('.ff-upload-preview').val();
		var file = jQuery('.ff-upload-preview').data('src');
		var additional_payable_services = jQuery( ".ff-el-form-check-radio" ).val();
		var error_message = jQuery( ".error_textarea" ).val();

		if (email != '' && order_number != '' && product_key != ''){
			jQuery.ajax({
				url: ajax_object.url,
				method:'post',
				data: {'action': 'email_license_issue',
				   'email': email,
				   'company': company,
				   'firstname': firstname,
				   'surname': surname,
				   'order_number': order_number,
				   'product_key': product_key,
				   'file': file,
				   'additional_payable_services': additional_payable_services,
				   'error_message': error_message
				},
				dataType: 'JSON',
				success: function(res){
		            console.log("Mail:",res);
				}
			   });
		}

	});

	jQuery('#frm_new_user_reg').submit(function() {
		var username = jQuery( "#wlm_form_field_username" ).val();
		var email = jQuery( "#wlm_form_field_email" ).val();

		jQuery.ajax({
			url: ajax_object.url,
			method:'post',
			data: {'action': 'form_new_user_reg',
			   'all_access': 'no',
			   'username': username,
			   'email' : email
			},
			dataType: 'JSON',
			success: function(res){
				console.log("Res new user:",res);
			}
		   });
	});

	 /*Greek Translations*/ 
     if(ur_sc.includes("/el/")){
		jQuery(".menu_login a").html("Σύνδεση");
		jQuery(".menu_logout a").html("Αποσύνδεση");
	 }

});


