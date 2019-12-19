var INKGO_JSON_URL = 'https://json.inkgo.io/';
var INKGO_API 	= '';
var inkgo 	= {
	init: function(){
		$('#load-campaigns').click(function(event) {
			inkgo.product.load();
			setTimeout(function(){
				if(inkgo.product.data != undefined)
					$('#TB_window').addClass('inkgo-modal');
				else
					$('#TB_window').addClass('inkgo-modal inkgo-modal-loading');
			}, 100);
		});
		$('#inkgo-campaign-img').click(function(event) {
			inkgo.product.load();
			setTimeout(function(){
				if(inkgo.product.data != undefined)
					$('#TB_window').addClass('inkgo-modal');
				else
					$('#TB_window').addClass('inkgo-modal inkgo-modal-loading');
			}, 100);
		});
		$('#remove-campaign').click(function(event) {
			$(this).hide();
			$('#load-campaigns').show();
			$('.inkgo-meta-campaign_id').val('');
			$('.inkgo-meta-campaign_thumb').val('');
			$('.inkgo-meta-campaign_mockups').val('');
			$('#inkgo-campaign-img').html('');
		});
	},
	product: {
		mockups: {},
		load: function(){
			if(inkgo.product.data != undefined)
			{
				inkgo.product.addAll();
				return;
			}
			inkgo.getData('campaigns', this.addAll);
		},
		addAll: function(data){
			if(data != undefined)
			{
				inkgo.product.data = data;
			}
			$('.inkgo-modal').removeClass('inkgo-modal-loading');
			var html 	= '<ul class="attachments">';
			$.each(inkgo.product.data, function(i, row){
				html = html + 	'<li class="attachment inkgo-product" data-id="'+row.campaign_id+'" data-user_id="'+row.user_id+'">';

				html = html + 		'<div class="attachment-preview" title="'+row.name+'">';
				html = html + 			'<div class="thumbnail">';
				html = html + 				'<div class="centered">';
				html = html + 					'<img src="'+inkgo.getSRC(row.thumb)+'" alt="">';
				html = html + 				'</div>';
				html = html + 			'</div>';
				html = html + 		'</div>';

				html = html + 	'</li>';
			});
			html = html + '</ul>';

			$('.inkgo-campaigns').html(html);
			$('.inkgo-product').click(function(event) {
				inkgo.product.add(this);
			});
		},
		add: function(e){
			var id = $(e).data('id');
			inkgo.product.user_id = $(e).data('user_id');

			$('.inkgo-meta-campaign_id').val(id);
			var div = $('#inkgo-campaign-img');

			var src 	= $(e).find('img').attr('src');
			$('.inkgo-meta-campaign_thumb').val(src);

			if(div.find('img').length > 0)
			{
				div.find('img').attr('src', src);
			}
			else
			{
				div.append('<img src="'+src+'" alt="">');
			}
			$('#load-campaigns').hide();
			$('#remove-campaign').show();

			$('.inkgo-modal').addClass('inkgo-modal-loading');

			if(inkgo.product.mockups[id] == undefined)
				inkgo.getData('design/'+id, inkgo.product.mockup, false);
			else
				inkgo.product.mockup();
		},
		mockup: function(data){
			var design_id = $('.inkgo-meta-campaign_id').val();
			if(data != undefined)
			{
				inkgo.product.mockups[design_id] = data;
			}

			var thumbs = {};
			jQuery.each(inkgo.product.mockups[design_id].mockups, function(product_id, mockups){
				jQuery.each(mockups, function(index, mockup) {
					var id 		= design_id+'-'+product_id+'-'+index;
					thumbs[id] 	= inkgo.product.user_id+'/design/'+design_id+'/mockup-'+product_id+'-'+index+'.png';
				});
			});
			var thumbs_json = JSON.stringify(thumbs);
			$('.inkgo-meta-campaign_mockups').val(thumbs_json);

			$('.inkgo-modal').removeClass('inkgo-modal-loading');
			$('#TB_closeWindowButton').trigger('click');
		}
	},
	getData: function(type, callback, no_api){

		if(no_api == undefined)
		{
			var url = INKGO_JSON_URL +'/'+ INKGO_API +'/'+ type;
		}
		else
		{
			var url = INKGO_JSON_URL +'/'+ type;
		}
		$.getJSON(url +'.json', function(result) {
			if(result.data != undefined)
			{
				callback(result.data);
			}
		});
	},
	getSRC: function(url){
		if(url.indexOf('http') == 0)
			var src = url;
		else
			var src = 'https://cdn.inkgo.io/'+url;

		return src;
	},
	variations: function(){
		jQuery('.inkgo-thumb').each(function(index, el) {
			var html = jQuery(this).html();
			var p = jQuery(this).parents('.woocommerce_variable_attributes').find('.form-row-first.upload_image');
			p.unbind();
			p.html(html);
			p.find('a').unbind();
			jQuery(this).remove();
		});
	}
}
jQuery(document).ready(function($) {
	INKGO_API = jQuery('#inkgo-apikey').val();
	inkgo.init();

	jQuery( '#woocommerce-product-data' ).on( 'woocommerce_variations_loaded', function(){
		inkgo.variations();
	});
});