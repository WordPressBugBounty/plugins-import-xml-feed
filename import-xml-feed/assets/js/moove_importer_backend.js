(function($) {
    var file,
        selected_node,
        xml_content_json,
        item_counter        = 0,
        import_max_length   = 0,
        import_queue        = {},
        import_index        = 0,
        import_started      = false,
        ajax_action         = "";
    $.fn.get_xml_content_json = function(){
      return xml_content_json;
    };

    $.fn.get_xml_file_content = function(){
      return file;
    };

    $.fn.get_xml_selected_node = function(){
      return selected_node;
    };

    $.fn.moove_imported_get_preview = function(){

        var old_xml = $('#moove_importer_feed_src').val();
        var old_node = $('#moove_importer_selected_node').val();
        MooveReadXML(old_xml,'url','preview', old_node );

    };

    var deactivation_started = false;

    $(document).on('click','.button-xml-deactivate-licence, .xml_deactivate_license_key',function(e){
      if ( ! deactivation_started ) {
        e.preventDefault();
        $('.xml-admin-popup.xml-admin-popup-deactivate').fadeIn(200);
      } else {
        $(this).closest('form').submit();
      }
    });

    $(document).on('click','.xml-admin-popup .xml-popup-overlay, .xml-admin-popup .xml-popup-close',function(e){
      e.preventDefault();
      $(this).closest('.xml-admin-popup').fadeOut(200);
    });

    $(document).on('click','.xml-admin-popup.xml-admin-popup-deactivate .button-deactivate-confirm',function(e){
      e.preventDefault();
      deactivation_started = true;
      $("<input type='hidden' value='1' />")
       .attr("id", "xml_deactivate_license")
       .attr("name", "xml_deactivate_license")
       .appendTo("#moove_xml_license_settings");
      $('#moove_xml_license_settings').submit();
      $(this).closest('.xml-admin-popup').fadeOut(200);
    });

    $(document).on('click','.xml-cookie-alert .xml-dismiss', function(e){
      e.preventDefault();
      $(this).closest('.xml-cookie-alert').slideUp(400);
      var ajax_url = $(this).attr('data-adminajax');
      var user_id = $(this).attr('data-uid');

      jQuery.post(
        ajax_url,
        {
          action: 'moove_hide_language_notice',
          user_id: user_id
        },
        function( msg ) {

        }
      );
    });


    $(document).on('click','.xml-cookie-update-alert .xml-dismiss-update', function(e){
      e.preventDefault();
      $(this).closest('.xml-cookie-alert').slideUp(400);
      var ajax_url  = $(this).attr('data-adminajax');
      var version   = $(this).attr('data-version');

      jQuery.post(
        ajax_url,
        {
          action: 'moove_hide_update_notice',
          version: version
        },
        function( msg ) {

        }
      );
    });

    function MooveReadXML(data, type, xmlaction, node) {
        ajax_action = 'read';
        $.post(
            ajaxurl,
            {
                action: "moove_read_xml",
                data: data,
                type: type,
                xmlaction: xmlaction,
                node: node,
                nonce: $('#moove_xml_admin_nonce').val(),
            },
            function(msg) {
                if ( xmlaction === 'check') {
                   msg = JSON.parse(msg);
                    if ( msg.response !== 'false' ) {
                        $('.moove-feed-xml-node-select .node-select-cnt').empty().append(msg.select_nodes).parent().show();
                        $('.moove-feed-importer-src-form').hide();
                        selected_node = msg.selected_element;
                        $('.select_another_source').parent().removeClass('moove-hidden');
                    } else {
                        invalid_xml_action();
                    }
                } else if ( xmlaction === 'preview' ) {
                    msg = JSON.parse(msg);
                    xml_content_json = JSON.parse(msg.xml_json_data);
                    $('.moove-feed-xml-preview-container').empty().append(msg.content);
                    $('.moove-importer-dynamic-select').empty().append(msg.select_option);
                    $('.moove-feed-xml-error').addClass('moove-hidden');
                    $('.moove-feed-xml-cnt').slideToggle('fast');
                    $('.moove-feed-xml-preview').slideToggle('fast');
                    $('.moove-feed-importer-where').removeClass('moove-hidden');
                }
            }
        )
    }

    function MooveCreatePostFromQueue() {
        if ( import_queue !== undefined && import_queue.length !== 0 ) {
            var import_index = Object.keys(import_queue)[0];
            var item = import_queue[import_index];
            delete import_queue[import_index];
            if ( typeof item !== 'undefined' ) {
                MooveCreatePostAjax( item.key, item.value, item.post_data_select, item.import_length );
                import_index++;
            }
        }
        
    }

    function MooveCreatePostAjax( key, value, post_data_select, import_length ) {
        if ( key ) {
            $.post(
                ajaxurl,
                {
                    action: "moove_create_post",
                    key : key,
                    value : value,
                    form_data : post_data_select,
                    nonce: $('#moove_xml_admin_nonce').val(),
                },
                function(msg) {
                    item_counter = item_counter + 1;                    
                    var percentage =  Math.ceil(item_counter*(100/import_length));
                    $('.moove-importer-ajax-import-progress-bar span').css('width',percentage+'%');
                    $('.moove-importer-percentage').text(percentage+'%');
                    if ( percentage == 100 ) {
                        $('.moove-importer-ajax-import-overlay').removeClass('import-work-on').find('h2').text('Feed Import Finished').parent().find('h4').text('Thank you for choosing our services');
                        $('.moove-importer-percentage').text(percentage+'%');
                        $('.moove-importer-ajax-import-progress-bar span').css('width',percentage+'%');
                    }
                    MooveCreatePostFromQueue();
                }
            );
        }
    }

    function MooveCreatePost( key, value, post_data_select, import_length ) {
        ajax_action = 'import';
        var current_post = ({
            'key'                   : key,
            'value'                 : value,
            'post_data_select'      : post_data_select,
            'import_length'         : import_length
        });
        import_queue[key] = current_post;
        if ( import_started === false ) {
            import_max_length = parseInt( import_length );
            import_index = key;            
            MooveCreatePostFromQueue();
            import_started = true;
        }
    }
    function getXmlString(xml) {
        var string = new XMLSerializer().serializeToString(xml.documentElement);
        return string;
    }

    function invalid_xml_action() {
        $('.moove-feed-xml-error').removeClass('moove-hidden');
    }

    function moove_array_to_object(arr) {
      var rv = {};
      for (var i = 0; i < arr.length; ++i)
        if (arr[i] !== undefined) rv[i] = arr[i];
      return rv;
    }
    $(document).ready(function() {

        $("input[name=moove-importer-feed-src]:radio").change(function() {
            $('.moove-to-hide').toggleClass('moove-hidden');
        });

        function moove_read_xml_file(e) {
            var files = e.target.files;
            var reader = new FileReader();
            reader.onload = function() {
                var parsed = new DOMParser().parseFromString(this.result, "text/xml");
                file = parsed;
            };
            reader.readAsText(files[0]);
        }

        $('.moove-importer-create-preview').on('click', function(e){
            e.preventDefault();
            if ( $('input[name=moove-importer-feed-src]:checked', '.moove-feed-importer-from').val() == 'url' ) {
                xml = $('#moove_importer_url').val();
                MooveReadXML(xml,'url','preview', selected_node );
            } else {
                MooveReadXML(getXmlString(file),'file','preview', selected_node);
            }
        });

        $('#moove-importer-post-type-select').on('change',function(e){
            var selected = $(this).find('option:selected').val();
            if ( selected !== '0' ) {
                $('.moove-feed-importer-taxonomies').removeClass('moove-hidden');
                $('.moove_cpt_tax').addClass('moove-hidden');
                $('.moove_cpt_tax_'+selected+'_settings').removeClass('moove-hidden');
                $('.moove_cpt_tax_'+selected+'_acf').removeClass('moove-hidden');
                $('.moove_cpt_tax_'+selected+'_customfields').removeClass('moove-hidden');
                $('.moove_cpt_tax_'+selected).removeClass('moove-hidden');
                $('.moove-submit-btn-cnt').removeClass('moove-hidden');
            } else {
                $('.moove-feed-importer-taxonomies').addClass('moove-hidden');
                $('.moove-submit-btn-cnt').addClass('moove-hidden');
            }
        });

        $('.moove-start-import-feed').on('click',function(e){
            e.preventDefault();

            var post_selected = $('#moove-importer-post-type-select option:selected').val();
            var taxonomies = [];
            var acf = [];
            var customfields = [];
            var cfcounter = 0;
            if ( $('#moove-importer-post-type-posttitle option:selected').val() !== "0") {
                $('.moove-feed-xml-cnt .moove-title-error').empty();
                $('.moove_cpt_tax_'+post_selected+' .moove-importer-taxonomy-box').each(function( index, value ){
                    taxonomies[index] = ({
                        taxonomy    :   $(this).attr('data-taxonomy'),
                        title       :   $(this).find('select.moove-importer-taxonomy-title option:selected').val()
                    });
                });

                $('.moove_cpt_tax_'+post_selected+'_acf.moove-importer-accordion .moove-importer-taxonomy-box').each(function( index, value ){
                    cfcounter++;
                    acf[cfcounter] = ({
                        field       :   $(this).find('.moove-importer-acf-type-select').val(),
                        value       :   $(this).find('.moove-importer-acf-xml-select').val()
                    });
                });

                $('.moove_cpt_tax_'+post_selected+'_customfields.moove-importer-accordion .moove-customfield-existing').each(function( index, value ){
                    customfields[index] = ({
                        field       :   $(this).find('.moove-customfield-dynamic-field').val(),
                        value       :   $(this).find('.moove-importer-customfield-xml-select').val()
                    });
                });

                var post_data_select = ({
                    post_type           :   $('#moove-importer-post-type-select option:selected').val(),
                    post_status         :   $('#moove-importer-post-type-status option:selected').val(),
                    post_title          :   $('#moove-importer-post-type-posttitle option:selected').val(),
                    post_date           :   $('#moove-importer-post-type-postdate option:selected').val(),
                    post_content        :   $('#moove-importer-post-type-postcontent option:selected').val(),
                    post_excerpt        :   $('#moove-importer-post-type-postexcerpt option:selected').val(),
                    post_featured_image :   $('#moove-importer-post-type-ftrimage option:selected').val(),
                    post_author         :   $('#moove-importer-post-type-author option:selected').val(),
                    taxonomies          :   moove_array_to_object(taxonomies),
                    acf                 :   moove_array_to_object(acf),
                    customfields        :   moove_array_to_object(customfields)
                });
                $('.moove-feed-importer-where').hide();
                $('.moove-feed-importer-from').hide();
                $('.moove-importer-ajax-import-overlay').slideToggle('fast');
                var import_limit = $('.moove_cpt_tax_'+post_selected+'_settings input[name="moove_feed_importer_limit"]').val();
                if ( import_limit ) {
                    var enabled_ids = [];
                    var limit_export = import_limit.split(";");
                    if ( limit_export && Array.isArray(limit_export) ) {
                        for (var i = 0; i < limit_export.length; i++) {
                            var limit_sections = limit_export[i].split("-");
                            if ( limit_sections.length === 1 ) {
                                enabled_ids.push(parseInt(limit_sections[0]));
                            } else if (limit_sections.length === 2 ) {
                                var start = limit_sections[0];
                                var end = parseInt(limit_sections[1]);
                                if ( limit_sections[1] === '' ) {
                                    end = Object.keys(xml_content_json).length;
                                }
                                for (var j = start; j <= end; j++) {
                                    enabled_ids.push( parseInt( j ) );
                                }
                            }
                        }
                    }
                }
                var import_counter = 0;
                $.each(xml_content_json, function(key, value) {
                    import_counter++;
                    if ( Array.isArray( enabled_ids ) ) {
                        if ( $.inArray( import_counter, enabled_ids ) > -1 ) {
                            MooveCreatePost( key, value, post_data_select, enabled_ids.length );
                        }
                    } else {
                        MooveCreatePost( key, value, post_data_select, Object.keys(xml_content_json).length );
                    }

                });

            } else {
                $('.moove-feed-importer-taxonomies .moove-title-error').text('Please select a field for title');
                $('#moove-importer-post-type-posttitle').focus();
            }

        });
        $('.moove-feed-importer-taxonomies').on('change','#moove-importer-post-type-posttitle',function(){
            var selected = $(this).find('option:selected').val();
            if ( selected !== '0' ) {
                $('.moove-title-error').empty();
            } else {
                $('.moove-feed-importer-taxonomies .moove-title-error').text('Please select a field for title');
            }
        });
        $('.moove-feed-xml-cnt').on('change','.node-select-cnt select',function(){
            selected_node = $(this).find('option:selected').val();
        });
        $('.moove-importer-read-file').on('click',function(e){
            e.preventDefault();
            if ( $('input[name=moove-importer-feed-src]:checked', '.moove-feed-importer-from').val() == 'url' ) {
                xml = $('#moove_importer_url').val();
                var ext = xml.substr(xml.lastIndexOf('.') + 1);

                MooveReadXML(xml,'url','check','');

            } else {
                var ext = $('#moove_importer_file').val().split('.').pop().toLowerCase();
                if($.inArray(ext, ['xml','rss']) == -1) {
                    invalid_xml_action();
                } else {
                    if ( typeof file !== 'undefined') {
                        MooveReadXML(getXmlString(file),'file','check','');
                    } else {
                        invalid_xml_action();
                    }
                }
            }
        });
        if ($('#moove_importer_file').length) {
            document.getElementById("moove_importer_file").addEventListener("change", moove_read_xml_file, false );
        }
        $('.select_another_source').on('click',function(e){
            e.preventDefault();
            $('.moove-feed-importer-src-form').trigger('reset');
            $('.moove-feed-xml-node-select').hide();
            $('.moove-feed-importer-src-form').show();
            $('.moove-feed-importer-where').addClass('moove-hidden');
            $('.moove-importer-src-upload').addClass('moove-hidden');
            $('.moove-importer-src-url').removeClass('moove-hidden');
            $('.moove_cpt_tax').addClass('moove-hidden');
            $('.moove-feed-xml-preview').hide();
            $('.moove-feed-importer-where').addClass('moove-hidden');
            $('.moove-feed-xml-cnt').show();
            $(this).parent().addClass('moove-hidden');
        });

        $('.moove-feed-xml-preview-container').on( 'click', '.moove-xml-preview-pagination', function(e) {
            e.preventDefault();
            $active = $('.moove-feed-xml-preview-container .moove-importer-readed-feed.moove-active');
            if ( ! $(this).hasClass('button-disabled') ) {
                $('.moove-xml-preview-pagination').removeClass('button-disabled');
                if ( $(this).hasClass('button-next') ) {
                    $(this).parent().attr('data-current', parseInt($(this).parent().attr('data-current'))+1);
                    $('.moove-importer-readed-feed.moove-active').addClass('moove-hidden').removeClass('moove-active').next('.moove-importer-readed-feed').addClass('moove-active').removeClass('moove-hidden');
                } else {
                    $(this).parent().attr('data-current',parseInt($(this).parent().attr('data-current'))-1);
                    $('.moove-importer-readed-feed.moove-active').addClass('moove-hidden').removeClass('moove-active').prev('.moove-importer-readed-feed').addClass('moove-active').removeClass('moove-hidden');
                }
                if ( $('.moove-importer-readed-feed.moove-active').attr('data-no') == $('.moove-importer-readed-feed.moove-active').attr('data-total') ) {
                    $('.moove-feed-xml-preview-container .moove-xml-preview-pagination.button-next').addClass('button-disabled');
                }
                if ( $('.moove-importer-readed-feed.moove-active').attr('data-no') == 1 ) {
                    $('.moove-feed-xml-preview-container .moove-xml-preview-pagination.button-previous').addClass('button-disabled');
                }
                $('.moove-form-container.feed_importer .pagination-info').text( ' '+$(this).parent().attr('data-current')+' / '+$('.moove-importer-readed-feed.moove-active').attr('data-total'));
            }
        });

        $('.moove-importer-accordion .moove-importer-accordion-header').on('click','a',function(e){

            if( $(e.target).is('.active') ) {
                moove_close_accordion($(this));
            } else {
                moove_close_accordion($(this));
                // Add active class to section title
                $(this).addClass('active');
                // Open up the hidden content panel
                $(this).closest('.moove-importer-accordion').find('.moove-importer-accordion-content').slideDown(300).addClass('open');
            }

            e.preventDefault();
        });

        function moove_close_accordion(button) {
            button.removeClass('active');
            button.closest('.moove-importer-accordion').find('.moove-importer-accordion-content').slideUp(300);
        }

        $(document).on('click','.moove_importer_add_acf_rule',function(e){
            e.preventDefault();
            if ( ! $(this).is('.disabled') ) {
                $(this).closest('.moove-importer-dynamic-accordion').find('.moove-initial-box').clone().appendTo($(this).closest('.moove-importer-accordion-content').find('.moove-importer-acf-rule-holder')).removeClass('moove-hidden').removeClass('moove-initial-box');
                $(this).addClass('disabled');
            }
        });

        $(document).on('change','.moove-importer-acf-box select',function(){
            if ( $(this).parent().find('.moove-importer-acf-xml-select').val() != 0 && $(this).parent().find('.moove-importer-acf-type-select').val() != 0 ) {
                $('.moove_importer_add_acf_rule').removeClass('disabled');
            } else {
                $('.moove_importer_add_acf_rule').addClass('disabled');
            }
        });

        $(document).on('click','.moove_importer_add_customfield_existing',function(e){
            e.preventDefault();
            if ( ! $(this).is('.disabled') ) {
                $(this).closest('.moove-importer-dynamic-accordion').find('.moove-initial-box-existing').clone().appendTo($(this).closest('.moove-importer-accordion-content').find('.moove-importer-customfield-rule-holder')).removeClass('moove-hidden').removeClass('moove-initial-box-existing');
            }
        });

        $(document).on('click','.moove_importer_add_customfield_new',function(e){
            e.preventDefault();
            if ( ! $(this).is('.disabled') ) {
                $(this).closest('.moove-importer-dynamic-accordion').find('.moove-initial-box-new').clone().appendTo($(this).closest('.moove-importer-accordion-content').find('.moove-importer-customfield-rule-holder')).removeClass('moove-hidden').removeClass('moove-initial-box-new');
            }
        });



        $(document).on('click','.moove_importer_remove_acf_group',function(e) {
            e.preventDefault();
            $(this).closest('.moove-importer-acf-box').remove();
            $('.moove_importer_add_acf_rule').removeClass('disabled');
        });
        $(document).on('click','.moove_importer_remove_customfield_group',function(e) {
            e.preventDefault();
            $(this).closest('.moove-importer-customfield-box').remove();
            $('.moove_importer_add_customfield_new').removeClass('disabled')
            $('.moove_importer_add_customfield_existing').removeClass('disabled')
        });

    }); // end document ready
    $(document).ajaxSend(function () {
        if ( ajax_action === 'read' ) {
            $('.moove-form-container.feed_importer').addClass('ajax-loading-process');
        }
    })
    .ajaxComplete(function () {
        if ( ajax_action === 'read' ) {
            $('.moove-form-container.feed_importer').removeClass('ajax-loading-process');
        }
    });
})(jQuery);

(function($) {
    $(document).ready(function() {
        function moove_array_to_object(arr) {
          var rv = {};
          for (var i = 0; i < arr.length; ++i)
            if (arr[i] !== undefined) rv[i] = arr[i];
          return rv;
        }
        var load_view = $("#moove_importer_selected_node");
        if ( load_view.length > 0 ) {
            $(document).moove_imported_get_preview();
            var preload_json_data = $("#moove_import_json").text();
            var json = JSON.parse( preload_json_data );
            console.warn(json);
            if ( typeof json.post_type !== 'undefined' ) {
                $('#moove-importer-post-type-select option[value="'+json.post_type+'"]').attr('selected','selected');
                $(document).ajaxComplete(function () {

                    setTimeout( set_importer_fields, 0 );

                });
                function set_importer_fields(){
                    // console.log(json);
                    $('#moove-importer-post-type-posttitle').val( json.post_title );
                    $('#moove-importer-post-type-postcontent').val( json.post_content );
                    $('#moove-importer-post-type-postdate').val( json.post_date );
                    $('#moove-importer-post-type-postexcerpt').val( json.post_excerpt );
                    $('#moove-importer-post-type-status').val( json.post_status );
                    $('#moove-importer-post-type-author').val( json.post_author );
                    $('#moove-importer-post-type-ftrimage').val( json.post_featured_image );
                    $('.moove_cpt_tax_post_settings input').val(json.limit);
                    $('#moove_importer_url').val( json.feedurl );
                    $('#moove-xml-nodes').val( json.selected_node );
                    $('.moove_cpt_tax_'+json.post_type+' .moove-importer-taxonomy-box').each(function( index, value ){
                        var taxonomy = $(this).attr('data-taxonomy');                        
                        var taxonomies = json.taxonomies;
                        var result = $.grep(taxonomies, function(e){ return e.taxonomy == taxonomy; });
                        if ( result.length ) {
                            $(this).find('select').val(result[0].title);
                        }
                    });

                    $('.moove_cpt_tax_'+json.post_type+'_customfields').each(function( index, value ){
                        var parent = $(this);          
                        parent.find('.box-loaded-via-ajax').remove();       
                        jQuery.each( json.customfields, function( i, val ) {
                            if ( val.field !== '0' && val.value !== '0' ) {
                                parent.find('.moove-initial-box-existing').clone().appendTo(parent.find('.moove-importer-customfield-rule-holder')).removeClass('moove-hidden').removeClass('moove-initial-box-existing').addClass('box-loaded-via-ajax').attr('data-bxid',i);

                                parent.find('[data-bxid='+i+'] select.moove-importer-customfield-type-select').val(val.field);
                                parent.find('[data-bxid='+i+'] select.moove-importer-customfield-xml-select').val(val.value);
                            }
                        });
                    });

                    $('.moove_cpt_tax_'+json.post_type+'_acf').each(function( index, value ){    
                        var parent = $(this);         
                        parent.find('.box-loaded-via-ajax').remove();             
                        jQuery.each( json.acf, function( i, val ) {
                            if ( val.field !== '0' && val.value !== '0' ) {
                                parent.find('.moove-initial-box').clone().appendTo(parent.find('.moove_importer_add_acf_rule').closest('.moove-importer-accordion-content').find('.moove-importer-acf-rule-holder')).removeClass('moove-hidden').addClass('box-loaded-via-ajax').removeClass('moove-initial-box').attr('data-bxid',i);
                                parent.find('[data-bxid='+i+'] select.moove-importer-acf-type-select').val(val.field);
                                parent.find('[data-bxid='+i+'] select.moove-importer-taxonomy-title').val(val.value);
                            }
                        });
                        
                    });
                }
                if ( json.post_type !== '0' ) {
                    $('.moove-feed-importer-taxonomies').removeClass('moove-hidden');
                    $('.moove_cpt_tax').addClass('moove-hidden');
                    $('.moove_cpt_tax_'+json.post_type+'_settings').removeClass('moove-hidden');
                    $('.moove_cpt_tax_'+json.post_type+'_acf').removeClass('moove-hidden');
                    $('.moove_cpt_tax_'+json.post_type+'_customfields').removeClass('moove-hidden');
                    $('.moove_cpt_tax_'+json.post_type).removeClass('moove-hidden');
                    $('.moove-submit-btn-cnt').removeClass('moove-hidden');
                } else {
                    $('.moove-feed-importer-taxonomies').addClass('moove-hidden');
                    $('.moove-submit-btn-cnt').addClass('moove-hidden');
                }
            }
        }
        var test = '';
        $('.moove-importer-plugin-wrap').on('click','.moove-save-as-template-btn',function(e){
            var $bnt = $(this);
            if ( ! $(this).is('.active') ) {
                $('.moove-importer-plugin-wrap .moove-importer-load-template').slideUp(300,function(){
                    $('.moove-importer-plugin-wrap .moove-importer-save-template').slideDown(300);
                    $('.moove-importer-plugin-wrap .moove-importer-load-template-btn').removeClass('active');
                    $bnt.addClass('active');
                });
            } else {
                $('.moove-importer-plugin-wrap .moove-importer-save-template').slideUp(300);
                $bnt.removeClass('active');
            }
            e.preventDefault();
        });

        $('.moove-importer-plugin-wrap').on('click','.moove-importer-load-template-btn',function(e){
            var $bnt = $(this);
            if ( ! $(this).is('.active') ) {
                $('.moove-importer-plugin-wrap .moove-importer-save-template').slideUp(300,function(){
                    $('.moove-importer-plugin-wrap .moove-importer-load-template').slideDown(300);
                    $('.moove-importer-plugin-wrap .moove-save-as-template-btn').removeClass('active');
                    $bnt.addClass('active');
                });
            } else {
                $('.moove-importer-plugin-wrap .moove-importer-load-template').slideUp(300);
                $bnt.removeClass('active');
            }
            e.preventDefault();
        });

        $('.moove-importer-plugin-wrap').on('click','a.moove-importer-save-template-action',function(e){
            var post_selected = $('#moove-importer-post-type-select option:selected').val();
            var taxonomies = [];
            var acf = [];
            var customfields = [];
            var cfcounter   = 0;
            var acfcounter  = 0;
            if ( $('#moove-importer-post-type-posttitle option:selected').val() !== "0") {
                $('.moove-feed-xml-cnt .moove-title-error').empty();
                $('.moove_cpt_tax_'+post_selected+' .moove-importer-taxonomy-box').each(function( index, value ){
                    taxonomies[index] = ({
                        taxonomy    :   $(this).attr('data-taxonomy'),
                        title       :   $(this).find('select.moove-importer-taxonomy-title option:selected').val()
                    });
                });

                $('.moove_cpt_tax_'+post_selected+'_acf.moove-importer-accordion .moove-importer-taxonomy-box').each(function( index, value ){
                    var field = $(this).find('.moove-importer-acf-type-select').val();
                    var value = $(this).find('.moove-importer-acf-xml-select').val();
                    if ( field !== '0' && value !== '0' ) {
                        acfcounter++;
                        acf[acfcounter] = ({
                            field       :   field,
                            value       :   value
                        });
                    }
                });

                $('.moove_cpt_tax_'+post_selected+'_customfields.moove-importer-accordion .moove-customfield-existing').each(function( index, value ){
                    var field = $(this).find('.moove-customfield-dynamic-field').val();
                    var value = $(this).find('.moove-importer-customfield-xml-select').val();
                    if ( field !== '0' && value !== '0' ) {
                        cfcounter++;
                        customfields[cfcounter] = ({
                            field       :   field,
                            value       :   value
                        });
                    }
                });


                var post_data_select = ({
                    post_type           :   $('#moove-importer-post-type-select option:selected').val(),
                    post_status         :   $('#moove-importer-post-type-status option:selected').val(),
                    post_title          :   $('#moove-importer-post-type-posttitle option:selected').val(),
                    post_date           :   $('#moove-importer-post-type-postdate option:selected').val(),
                    post_content        :   $('#moove-importer-post-type-postcontent option:selected').val(),
                    post_excerpt        :   $('#moove-importer-post-type-postexcerpt option:selected').val(),
                    post_featured_image :   $('#moove-importer-post-type-ftrimage option:selected').val(),
                    post_author         :   $('#moove-importer-post-type-author option:selected').val(),
                    taxonomies          :   moove_array_to_object(taxonomies),
                    acf                 :   moove_array_to_object(acf),
                    customfields        :   moove_array_to_object(customfields)
                });

                var type = $('input[name=moove-importer-feed-src]:checked', '.moove-feed-importer-from').val();
                var url = '';
                var file = '';
                var xmlText = '';
                if ( type === 'url' ) {
                    url = $('#moove_importer_url').val();
                } else {
                    file = $(this).get_xml_file_content();
                    xmlText = new XMLSerializer().serializeToString(file);
                }

                var content_json = $(this).get_xml_content_json();

                $.post(
                    ajaxurl,
                    {
                        action: "moove_save_import_template",
                        form_data : post_data_select,
                        name : $('#moove_importer_template_name').val(),
                        url : url,
                        type : type,
                        file : xmlText,
                        limit: $('input[name="moove_feed_importer_limit"]').val(),
                        selected_node : $('#moove-xml-nodes').val(),
                        extension : $('#moove_importer_file').val().split('.').pop().toLowerCase(),
                        nonce: $('#moove_xml_admin_nonce').val(),
                    },
                    function(msg) {
                        // console.log(msg);
                        obj = JSON.parse(msg);
                        if ( obj.success == 'true' ) {
                            $('#moove_importer_load_template').append($('<option>', {
                                value: obj.template_id,
                                text : obj.slug
                            }));
                        }
                    }
                );

                //console.log(post_data_select);

            } else {
                $('.moove-feed-importer-taxonomies .moove-title-error').text('Please select a field for title');
                $('#moove-importer-post-type-posttitle').focus();
            }

            e.preventDefault();
        });

        $('.moove-importer-plugin-wrap').on('click','a.moove-importer-load-template-action',function(e){
            var template_id = $('#moove_importer_load_template').val();
            if ( template_id ) {
                 $.post(
                    ajaxurl,
                    {
                        action: "moove_load_import_template",
                        template_id : template_id,
                        nonce: $('#moove_xml_admin_nonce').val(),
                    },
                    function(msg) {
                        // console.log(msg);
                        obj = JSON.parse(msg);
                        if ( obj.success == 'true' ) {
                            var data = obj.data;
                            // console.log(data);
                            $('.moove_cpt_tax_post_settings input').val('');                            
                            $.each(data,function(key,value){
                                console.log(key);
                                switch(key) {
                                    case 'post_type':
                                        $('#moove-importer-post-type-select option[value="'+value+'"]').attr('selected', 'selected');
                                        if ( value !== '0' ) {
                                            $('.moove-feed-importer-taxonomies').removeClass('moove-hidden');
                                            $('.moove_cpt_tax').addClass('moove-hidden');
                                            $('.moove_cpt_tax_'+value).removeClass('moove-hidden');
                                            $('.moove-submit-btn-cnt').removeClass('moove-hidden');
                                        } else {
                                            $('.moove-feed-importer-taxonomies').addClass('moove-hidden');
                                            $('.moove-submit-btn-cnt').addClass('moove-hidden');
                                        }

                                        break;
                                    case 'post_status':
                                        $('#moove-importer-post-type-status option[value="'+value+'"]').attr('selected', 'selected');
                                        break;
                                    case 'post_title':
                                        $('#moove-importer-post-type-posttitle option[value="'+value+'"]').attr('selected', 'selected');
                                        break;
                                    case 'post_content':
                                        $('#moove-importer-post-type-postcontent option[value="'+value+'"]').attr('selected', 'selected');
                                        break;
                                    case 'post_date':
                                        $('#moove-importer-post-type-postdate option[value="'+value+'"]').attr('selected', 'selected');
                                        break;
                                    case 'post_excerpt':
                                        $('#moove-importer-post-type-postexcerpt option[value="'+value+'"]').attr('selected', 'selected');
                                        break;
                                    case 'post_author':
                                        $('#moove-importer-post-type-author option[value="'+value+'"]').attr('selected', 'selected');
                                        break;
                                    case 'post_featured_image':
                                        $('#moove-importer-post-type-ftrimage option[value="'+value+'"]').attr('selected', 'selected');
                                        break;
                                    case 'limit':
                                        $('.moove_cpt_tax_post_settings input').val(value);
                                        break;
                                    case 'customfields': 
                                        var fields = value;

                                        $('.moove_cpt_tax_'+data.post_type+'_customfields').each(function( _index, _value ){

                                            var parent = $(this);       
                                            parent.find('.box-loaded-via-ajax').remove();     
                                            jQuery.each( fields, function( i, val ) {
                                                if ( val.field !== '0' && val.value !== '0' ) {
                                                    
                                                    parent.find('.moove-initial-box-existing').clone().appendTo(parent.find('.moove-importer-customfield-rule-holder')).removeClass('moove-hidden').removeClass('moove-initial-box-existing').addClass('box-loaded-via-ajax').attr('data-bxid',i);

                                                    parent.find('[data-bxid='+i+'] select.moove-importer-customfield-type-select').val(val.field);
                                                    parent.find('[data-bxid='+i+'] select.moove-importer-customfield-xml-select').val(val.value);
                                                }
                                            });
                                        }).removeClass('moove-hidden');


                                        break;
                                    case 'acf': 
                                        var fields = value;
                                        $('.moove_cpt_tax_'+data.post_type+'_acf').each(function( _index, _value ){    
                                            var parent = $(this); 
                                            parent.find('.box-loaded-via-ajax').remove();               
                                            jQuery.each( fields, function( i, val ) {                                                
                                                if ( val.field !== '0' && val.value !== '0' ) {                                                    
                                                    parent.find('.moove-initial-box').clone().appendTo(parent.find('.moove_importer_add_acf_rule').closest('.moove-importer-accordion-content').find('.moove-importer-acf-rule-holder')).removeClass('moove-hidden').removeClass('moove-initial-box').addClass('box-loaded-via-ajax').attr('data-bxid',i);
                                                    parent.find('[data-bxid='+i+'] select.moove-importer-acf-type-select').val(val.field);
                                                    parent.find('[data-bxid='+i+'] select.moove-importer-taxonomy-title').val(val.value);
                                                }
                                            });
                                            
                                        }).removeClass('moove-hidden');

                                        break;                                        
                                    case 'taxonomies':
                                        var taxonomies = value;
                                        $.each(taxonomies,function(tax_key, tax_value) {
                                            var $parent = $('.moove-importer-taxonomy-box[data-taxonomy="'+tax_value.taxonomy+'"]');
                                            $parent.find('option[value="'+tax_value.title+'"]').attr('selected', 'selected');
                                        });
                                        break;
                                    default:

                                }

                            });
                            $('.moove_cpt_tax_post_settings.moove-importer-accordion').removeClass('moove-hidden');
                        }
                    }
                );
            }
            e.preventDefault();
        });

        $('.moove-importer-plugin-wrap').on('click','.moove_importer_templates a.submitdelete',function(e){
            var template_id = $(this).attr('data-templateid');
            var $btn = $(this);
            if ( template_id ) {
                if (confirm('Are you sure?')) {
                    $.post(
                        ajaxurl,
                        {
                            action: "moove_delete_import_template",
                            template_id : template_id,
                            nonce: $('#moove_xml_admin_nonce').val(),
                        },
                        function(msg) {
                            // console.log(msg);
                            obj = JSON.parse(msg);
                            if ( obj.success == 'true' ) {
                                $btn.closest('tr').remove();
                            }
                        }
                    );
                }
            }
            e.preventDefault();
        });
    });
})(jQuery);
